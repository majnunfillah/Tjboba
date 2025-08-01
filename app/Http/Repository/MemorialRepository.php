<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\MemorialInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class MemorialRepository extends BaseRepository implements MemorialInterface
{
    public function getAllMemorial()
    {
        try {
            \Log::info('getAllMemorial function is called.');
           
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            
            if (!$periode) {
                return collect([]);
            }
            
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;
            return DB::select("select A.NoBukti, A.Tanggal, A.Note, '' Devisi, '' Perkiraan,
        sum(case when B.Valas='IDR' then 0.00 else B.Debet+B.Kredit end) TotalD,
        sum((B.Debet+B.Kredit)*B.Kurs) TotalRp,
	A.IsOtorisasi1, A.OtoUser1, A.TglOto1, A.IsOtorisasi2, A.OtoUser2, A.TglOto2, 
	A.IsOtorisasi3, A.OtoUser3, A.TglOto3, A.IsOtorisasi4, A.OtoUser4, A.TglOto4,
	A.IsOtorisasi5, A.OtoUser5, A.TglOto5,
        Cast(Case when Case when A.IsOtorisasi1=1 then 1 else 0 end+
                       Case when A.IsOtorisasi2=1 then 1 else 0 end+
                       Case when A.IsOtorisasi3=1 then 1 else 0 end+
                       Case when A.IsOtorisasi4=1 then 1 else 0 end+
                       Case when A.IsOtorisasi5=1 then 1 else 0 end=A.MaxOL then 0
                  else 1
             end As Bit) NeedOtorisasi
            from dbTrans A
            left join dbTransaksi B on B.NoBukti=A.NoBukti
            where year(A.Tanggal)=" . $periode->TAHUN . " and month(A.Tanggal)=" . $periode->BULAN . "
                    and A.TipeTransHD in ('BMM','BJK','PBL','PJL')  and isnull(flagtipe,0)=0
            group by A.NoBukti, A.Tanggal, A.Note,
	A.IsOtorisasi1, A.OtoUser1, A.TglOto1, A.IsOtorisasi2, A.OtoUser2, A.TglOto2, 
	A.IsOtorisasi3, A.OtoUser3, A.TglOto3, A.IsOtorisasi4, A.OtoUser4, A.TglOto4,
	A.IsOtorisasi5, A.OtoUser5, A.TglOto5, MaxOL
Order by A.Nobukti");
            
        } catch (QueryException $ex) {
            \Log::error('Error in getAllMemorial: ' . $ex->getMessage());
            throw $ex;
        }
    }


   
  



    public function update($data)
    {
        try {
            return DB::table('memorials')->where('NoBukti', $data['NoBukti'])->update($data);
        } catch (QueryException $ex) {
            return abort(501, 'Error: ' . $ex->getMessage() . ' di Baris: ' . $ex->getLine());
        }
    }

    public function delete($NoBukti)
    {
        try {
            return DB::table('memorials')->where('NoBukti', $NoBukti)->delete();
        } catch (QueryException $ex) {
            return abort(501, 'Error: ' . $ex->getMessage() . ' di Baris: ' . $ex->getLine());
        }
    }
    
    public function store($request)
    {
        try {
            \Log::info('Memorial Repository Store Start', $request->all());
            
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            
            $request->validate([
                'NoBukti' => ['required', 'string', 'max:30'],
                'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
                'Note' => ['required', 'string', 'max:500'],
                'TipeTransHd' => ['required', 'in:BMM'],

            ]);

            $NoBukti = $request->NoBukti;

            \Log::info('TipeTransHd value:', ['TipeTransHd' => $request->TipeTransHd]);
            if ($request->nextNoBukti) {
                $NoBukti = $this->getNomorBukti($request->TipeTransHd)->NoBukti;
            }

            $data = $this->queryModel('dbtrans')->where('NoBukti', $NoBukti)->firstOrNew();
            
            if ($data->NoBukti != null) {
                return abort(501, 'No Bukti sudah ada');
            }

            $data->NoBukti = $NoBukti;
            $data->Tanggal = $request->Tanggal;
            $data->Note = $request->Note;
            $data->NOURUT = $request->NoUrut;
            $data->TipeTransHd = $request->TipeTransHd;
            $data->PerkiraanHd = $request->PerkiraanHd;
            $data->Lampiran = 0;
            $data->IsOtorisasi1 = 0;
            $data->OtoUser1 = '';
            $data->IsOtorisasi2 = 0;
            $data->OtoUser2 = '';
            $data->IsOtorisasi3 = 0;
            $data->OtoUser3 = '';
            $data->IsOtorisasi4 = 0;
            $data->OtoUser4 = '';
            $data->IsOtorisasi5 = 0;
            $data->OtoUser5 = '';
            $data->MaxOL = -1;
            $data->save();

            \Log::info('Memorial Repository Store Success');
            return $data;

        } catch (\Exception $e) {
            \Log::error('Memorial Repository Store Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    public function storeMemorial($request)
    {
        DB::beginTransaction();
        try {
            $userid = auth()->user()->USERID;
            
            // Log incoming request data
            \Log::info('storeMemorial request data:', $request->toArray());

            // First check if trans record exists before validation
            $trans = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();
            
            if (!$trans) {
                \Log::error('Transaction header not found for NoBukti: ' . $request->NoBukti);
                throw new \Exception('Transaction header not found');
            }

            // Add TPHC to validation rules since it's used in sp_Transaksi
            $validatedData = $request->validate([
                'NoBukti' => ['required', 'string', 'max:30'],
                'Keterangan' => ['nullable', 'string', 'max:8000'],
                'Valas' => ['required', 'string', 'max:15'],
                'Kurs' => ['required', 'numeric'],
                'Debet' => ['required', 'numeric'],
                'KodeBag' => ['nullable', 'string', 'max:30'],
                'Perkiraan' => ['required', 'string', 'max:25'],
                'Lawan' => ['required', 'string', 'max:25'],
            ]);

            // Check if Perkiraan matches Lawan
            if ($request->Perkiraan === $request->Lawan) {
                throw new \Exception('Perkiraan and Lawan cannot be the same');
            }

            $count = $this->queryModel('dbtransaksi')
                ->where('NoBukti', $request->NoBukti)
                ->orderBy('Urut', 'desc')
                ->value('Urut') ?? 0;

            $count++;
            $DebetRp = floatval($request->Debet) * floatval($request->Kurs);
            $KodeBag = $request->KodeBag ?? '-';
            $TPHC = 'C'; // Default value if not provided

            // Update sesuai dengan definisi sp_Transaksi (32 parameter)
            $paramValues = [
                'Choice' => 'I',
                'NoBukti' => $request->NoBukti,
                'NoUrut' => $trans->NOURUT,
                'Tanggal' => $trans->Tanggal,
                'Note' => $trans->Note,
                'Lampiran' => '0',
                'Devisi' => '01',
                'Perkiraan' => $request->Perkiraan,
                'Lawan' => $request->Lawan,
                'Keterangan' => $request->Keterangan ?? '',
                'Keterangan2' => '',
                'Debet' => $request->Debet,
                'Kredit' => 0,
                'Valas' => $request->Valas,
                'Kurs' => $request->Kurs,
                'DebetRp' => $DebetRp,
                'KreditRp' => 0,
                'TipeTrans' => $trans->TipeTransHd,
                'TPHC' => 'C',
                'CustSuppP' => '',
                'CustSuppL' => '',
                'Urut' => $count,
                'NoAktivaP' => '',
                'NoAktivaL' => '',
                'StatusAktivaP' => '',
                'StatusAktivaL' => '',
                'NoBon' => '-',
                'KodeBag' => $KodeBag,
                'KodeP' => '',
                'KodeL' => '',
                'Statusgiro' => '',
                'Simbol' => ''
            ];

            // Generate SQL for direct MSSQL execution
            $sqlForCopy = "EXEC sp_Transaksi 
                N'" . $paramValues['Choice'] . "', 
                N'" . $paramValues['NoBukti'] . "', 
                N'" . $paramValues['NoUrut'] . "', 
                N'" . $paramValues['Tanggal'] . "', 
                N'" . $paramValues['Note'] . "', 
                N'" . $paramValues['Lampiran'] . "', 
                N'" . $paramValues['Devisi'] . "', 
                N'" . $paramValues['Perkiraan'] . "', 
                N'" . $paramValues['Lawan'] . "', 
                N'" . $paramValues['Keterangan'] . "', 
                N'" . $paramValues['Keterangan2'] . "', 
                " . $paramValues['Debet'] . ", 
                " . $paramValues['Kredit'] . ", 
                N'" . $paramValues['Valas'] . "', 
                " . $paramValues['Kurs'] . ", 
                " . $paramValues['DebetRp'] . ", 
                " . $paramValues['KreditRp'] . ", 
                N'" . $paramValues['TipeTrans'] . "', 
                N'" . $paramValues['TPHC'] . "', 
                N'" . $paramValues['CustSuppP'] . "', 
                N'" . $paramValues['CustSuppL'] . "', 
                " . $paramValues['Urut'] . ", 
                N'" . $paramValues['NoAktivaP'] . "', 
                N'" . $paramValues['NoAktivaL'] . "', 
                N'" . $paramValues['StatusAktivaP'] . "', 
                N'" . $paramValues['StatusAktivaL'] . "', 
                N'" . $paramValues['NoBon'] . "', 
                N'" . $paramValues['KodeBag'] . "', 
                N'" . $paramValues['KodeP'] . "', 
                N'" . $paramValues['KodeL'] . "', 
                N'" . $paramValues['Statusgiro'] . "', 
                N'" . $paramValues['Simbol'] . "'";

            // Log SEBELUM eksekusi DB statement
            \Log::info('SQL statement for MSSQL:', ['sql' => $sqlForCopy]);

            // Eksekusi DB statement - 32 parameter sesuai definisi sp_Transaksi
            DB::statement('exec sp_Transaksi ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', [
                'I',                        // @Choice
                $request->NoBukti,          // @NoBukti
                $trans->NOURUT,             // @NoUrut
                $trans->Tanggal,            // @Tanggal
                $trans->Note,               // @Note
                '0',                        // @Lampiran
                '01',                       // @Devisi
                $request->Perkiraan,        // @Perkiraan
                $request->Lawan,            // @Lawan
                $request->Keterangan ?? '', // @Keterangan
                '',                         // @Keterangan2
                $request->Debet,            // @Debet
                0,                          // @Kredit
                $request->Valas,            // @Valas
                $request->Kurs,             // @Kurs
                $DebetRp,                   // @DebetRp
                0,                          // @KreditRp
                $trans->TipeTransHd,        // @TipeTrans
                'C',                        // @TPHC
                '',                         // @CustSuppP
                '',                         // @CustSuppL
                $count,                     // @Urut
                '',                         // @NoAktivaP
                '',                         // @NoAktivaL
                '',                         // @StatusAktivaP
                '',                         // @StatusAktivaL
                '-',                        // @NoBon
                $KodeBag,                   // @KodeBag
                '',                         // @KodeP
                '',                         // @KodeL
                '',                         // @StatusGiro
                ''                          // @Simbol
            ]);
 
            DB::commit();
            return true;

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('storeMemorial Validation Error:', [
                'errors' => $e->errors(),
                'line' => $e->getLine()
            ]);
            throw $e;
        } catch (QueryException $ex) {
            DB::rollBack();
            \Log::error('storeMemorial QueryException:', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeMemorial Exception:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    public function getNomorBukti($tipe)
    {
        try {
            $periode = $this->queryModel('dbperiode')->where('USERID', auth()->user()->USERID)->first();
            if ($periode == null) {
                return abort(501, 'Error : Periode belum di set');
            }
            xdebug_break();
            $generate = $this->queryModel('dbnomor')->generateNoBukti($periode);
            $NoBukti = $generate[0];
            $reset = $generate[1];

            // Set where clause based on reset period
            if ($reset == 'Tahun') {
                $wherePeriode = "WHERE year(Tanggal) = $periode->TAHUN";
            } else if ($reset == 'Bulan') {
                $wherePeriode = " AND year(Tanggal) = $periode->TAHUN AND month(Tanggal) = $periode->BULAN";
            }

            // Set where clause for memorial types
            $wherTipe = " AND TipeTransHd = 'BMM'";

            $query = "SELECT TOP 1 Nobukti, TipeTransHd, Tanggal, NOURUT FROM dbtrans $wherePeriode $wherTipe ORDER BY NOURUT DESC";

            $trans = DB::select($query);

            if (count($trans) > 0) {
                $trans = $trans[0];
                $NoUrut = intval($trans->NOURUT) + 1;
            } else {
                $NoUrut = 1;
            }

            // Format NoUrut with leading zeros
            if (strlen($NoUrut) == 1) {
                $NoUrut = '0000' . $NoUrut;
            } else if (strlen($NoUrut) == 2) {
                $NoUrut = '000' . $NoUrut;
            } else if (strlen($NoUrut) == 3) {
                $NoUrut = '00' . $NoUrut;
            } else if (strlen($NoUrut) == 4) {
                $NoUrut = '0' . $NoUrut;
            }

            // Replace placeholders in NoBukti
            $NoBukti = str_replace('nomor_urut', $NoUrut, $NoBukti);
            $NoBukti = str_replace('kode_transaksi', $tipe, $NoBukti);

            return (object)[
                'NoBukti' => $NoBukti,
                'NoUrut' => $NoUrut,
                'Tahun' => $periode->TAHUN,
                'Bulan' => $periode->BULAN,
            ];
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function getMemorialByNoBukti($NoBukti)
    {
        try {
            $res = $this->queryModel('dbtrans')
                ->select('dbtrans.*')
                ->where('NoBukti', $NoBukti)
                ->firstOrNew();

            if ($res->exists) {
                $res->canEdit = true;
            }
            return $res;

        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }
    
    public function getMemorialDetailByNoBukti($NoBukti)
    {
        try {
            return $this->queryModel('dbtransaksi')
                ->selectRaw("
                    NoBukti,
                    Tanggal,
                    Urut,
                    Perkiraan,
                    Lawan,
                    Keterangan,
                    (Debet + Kredit) * Kurs as JumlahRp
                ")
                ->where('NoBukti', $NoBukti)
                ->orderBy('Urut', 'ASC')
                ->get();
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function getDetailMemorialByNoBukti($NoBukti, $Tanggal, $Urut)
    {
        try {
            $detail = $this->queryModel('dbtransaksi')
                ->selectRaw("
                    NoBukti,
                    Tanggal,
                    Urut,
                    Perkiraan,
                    Lawan,
                    ISNULL(Keterangan, '') as Keterangan,
                    Valas,
                    Kurs,
                    Debet,
                    Kredit,
                    DebetRp,
                    KreditRp,
                    TPHC,
                    ISNULL(KodeBag, '') as KodeBag
                ")
                ->where('NoBukti', $NoBukti)
                ->where('Urut', $Urut)
                ->first();

            if ($detail) {
                // Ambil nama perkiraan dan lawan untuk dropdown
                $perkiraanInfo = $this->queryModel('dbperkiraan')
                    ->select('Perkiraan', 'Keterangan as Description')
                    ->where('Perkiraan', $detail->Perkiraan)
                    ->first();
                
                $lawanInfo = $this->queryModel('dbperkiraan')
                    ->select('Perkiraan', 'Keterangan as Description')
                    ->where('Perkiraan', $detail->Lawan)
                    ->first();

                $detail->KeteranganPerkiraan = $perkiraanInfo->Description ?? '';
                $detail->KeteranganLawan = $lawanInfo->Description ?? '';
            }

            return $detail;
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function updateMemorial($request)
    {
        try {
            $userid = auth()->user()->USERID;
            
            // Ambil data header transaksi untuk mendapatkan Note, Tanggal, dan NoUrut
            $trans = $this->queryModel('dbtrans')
                ->where('NoBukti', $request->NoBukti)
                ->first();
            
            if (!$trans) {
                throw new \Exception('Transaction header not found');
            }
            
            // Update menggunakan stored procedure dengan 32 parameter sesuai definisi sp_Transaksi
            DB::statement('exec sp_Transaksi ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', [
                'U',                        // @Choice - Update
                $request->NoBukti,          // @NoBukti
                $trans->NOURUT,             // @NoUrut
                $trans->Tanggal,            // @Tanggal
                $trans->Note,               // @Note
                '0',                        // @Lampiran
                '01',                       // @Devisi
                $request->Perkiraan,        // @Perkiraan
                $request->Lawan,            // @Lawan
                $request->Keterangan ?? '', // @Keterangan
                '',                         // @Keterangan2
                $request->Debet,            // @Debet
                $request->Kredit ?? 0,      // @Kredit
                $request->Valas,            // @Valas
                $request->Kurs,             // @Kurs
                floatval($request->Debet) * floatval($request->Kurs),    // @DebetRp
                floatval($request->Kredit ?? 0) * floatval($request->Kurs), // @KreditRp
                'BMM',                      // @TipeTrans
                'C',                        // @TPHC
                '',                         // @CustSuppP
                '',                         // @CustSuppL
                $request->Urut,             // @Urut
                '',                         // @NoAktivaP
                '',                         // @NoAktivaL
                '',                         // @StatusAktivaP
                '',                         // @StatusAktivaL
                '-',                        // @NoBon
                $request->KodeBag ?? '-',   // @KodeBag
                '',                         // @KodeP
                '',                         // @KodeL
                '',                         // @StatusGiro
                ''                          // @Simbol
            ]);

            return true;
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function deleteMemorial($NoBukti, $Urut)
    {
        try {
            // Delete menggunakan stored procedure dengan 32 parameter sesuai definisi sp_Transaksi
            DB::statement('exec sp_Transaksi ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', [
                'D',                        // @Choice - Delete
                $NoBukti,                   // @NoBukti
                '',                         // @NoUrut
                '',                         // @Tanggal
                '',                         // @Note
                '',                         // @Lampiran
                '',                         // @Devisi
                '',                         // @Perkiraan
                '',                         // @Lawan
                '',                         // @Keterangan
                '',                         // @Keterangan2
                0,                          // @Debet
                0,                          // @Kredit
                '',                         // @Valas
                0,                          // @Kurs
                0,                          // @DebetRp
                0,                          // @KreditRp
                '',                         // @TipeTrans
                '',                         // @TPHC
                '',                         // @CustSuppP
                '',                         // @CustSuppL
                $Urut,                      // @Urut
                '',                         // @NoAktivaP
                '',                         // @NoAktivaL
                '',                         // @StatusAktivaP
                '',                         // @StatusAktivaL
                '',                         // @NoBon
                '',                         // @KodeBag
                '',                         // @KodeP
                '',                         // @KodeL
                '',                         // @StatusGiro
                ''                          // @Simbol
            ]);

            return true;
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function setOtorisasi($request)
    {
        try {
            $userid = auth()->user()->USERID;
            $field = $request->otoLevel == 'IsOtorisasi1' ? 'IsOtorisasi1' : 'IsOtorisasi2';
            $userField = $request->otoLevel == 'IsOtorisasi1' ? 'OtoUser1' : 'OtoUser2';
            $dateField = $request->otoLevel == 'IsOtorisasi1' ? 'TglOto1' : 'TglOto2';

            return $this->queryModel('dbtrans')
                ->where('NoBukti', $request->NoBukti)
                ->update([
                    $field => $request->status,
                    $userField => $request->status == 1 ? $userid : '',
                    $dateField => $request->status == 1 ? now() : null,
                ]);
        } catch (QueryException $ex) {
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }
    
    // Hutang Piutang Methods
    public function getDataHutang($kode, $lawan)
    {
        try {
            $userid = auth()->user()->USERID;
            if($lawan == 'HT'){
                $countHutangTemp = $this->queryModel('dbtemphutpiut')->where('KodeCustSupp', $kode)->where('IDUser', $userid)->count();
                $Hutang = DB::select("with cte as (select Pembayaran=sum(Debet) over(partition by NoFaktur), 
                Hutang=sum(Kredit) over(partition by NoFaktur), NoFaktur, NoRetur, TipeTrans, NoBukti, NoMsk, Urut, Tanggal, JatuhTempo, Debet, 
                Kredit, Valas, Kurs, KodeSales, Tipe, Perkiraan, Catatan, NoInvoice, KodeVls_, Kurs_, KursBayar, DebetD,
                KreditD, rn = row_number() over (partition by NoFaktur, Urut order by Urut ASC) 
                from DBHUTPIUT where KodeCustSupp ='$kode')
                SELECT * from cte where rn = 1 and Pembayaran < Hutang and Tipe ='$lawan'");
                $countHutang = count($Hutang);
                // dd($countHutang)
                if ($countHutangTemp < $countHutang) {
                    DB::beginTransaction();
                    foreach ($Hutang as $key => $value) {
                        if (count(DB::select("select NoFaktur from dbtemphutpiut where KodeCustSupp ='$kode' and NoFaktur = '$value->NoFaktur' and Urut = '$value->Urut'")) < 1) {
                            DB::statement("exec sp_TempHutPiut ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?", [
                                'I', $value->NoFaktur, $value->NoRetur, $value->TipeTrans, $kode, $value->NoBukti, $value->NoMsk, $value->Urut,
                                $value->Tanggal, $value->JatuhTempo, $value->Debet, $value->Kredit, $value->Valas, $value->Kurs, $value->KodeSales,
                                $value->Tipe, $value->Perkiraan, $value->Catatan, $userid, 'D', $value->NoInvoice, $value->KodeVls_, $value->Kurs_,
                                $value->KursBayar, $value->DebetD, $value->KreditD
                            ]);
                        }
                    }
                }
            }else if($lawan == 'PT'){
                // dd('test');
                $countPiutangTemp = $this->queryModel('dbtemphutpiut')->where('KodeCustSupp', $kode)->where('IDUser', $userid)->count();
                $Piutang = DB::select("with cte as (select Pembayaran=sum(Debet) over(partition by NoFaktur), 
                Hutang=sum(Kredit) over(partition by NoFaktur), NoFaktur, NoRetur, TipeTrans, NoBukti, NoMsk, Urut, Tanggal, JatuhTempo, Debet, 
                Kredit, Valas, Kurs, KodeSales, Tipe, Perkiraan, Catatan, NoInvoice, KodeVls_, Kurs_, KursBayar, DebetD,
                KreditD, rn = row_number() over (partition by NoFaktur, Urut order by Urut ASC) 
                from DBHUTPIUT where KodeCustSupp ='$kode')
                SELECT * from cte where rn = 1 and Pembayaran > Hutang and Tipe ='$lawan'");
                $countPiutang = count($Piutang);
                // dd($countPiutang)
                if ($countPiutangTemp < $countPiutang) {
                    DB::beginTransaction();
                    foreach ($Piutang as $key => $value) {
                        if (count(DB::select("select NoFaktur from dbtemphutpiut where KodeCustSupp ='$kode' and NoFaktur = '$value->NoFaktur' and Urut = '$value->Urut'")) < 1) {
                            DB::statement("exec sp_TempHutPiut ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?", [
                                'I', $value->NoFaktur, $value->NoRetur, $value->TipeTrans, $kode, $value->NoBukti, $value->NoMsk, $value->Urut,
                                $value->Tanggal, $value->JatuhTempo, $value->Debet, $value->Kredit, $value->Valas, $value->Kurs, $value->KodeSales,
                                $value->Tipe, $value->Perkiraan, $value->Catatan, $userid, 'D', $value->NoInvoice, $value->KodeVls_, $value->Kurs_,
                                $value->KursBayar, $value->DebetD, $value->KreditD
                            ]);
                        }
                    }
                }
            }
            DB::commit();

            return $this->queryModel('dbtemphutpiut')->where('KodeCustSupp', $kode)->where('Tipe', $lawan)->where('IDUser', $userid)->orderBy('NoFaktur', 'DESC')->get();
        } catch (QueryException $ex) {
            DB::rollBack();
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function pelunasanHutang($request)
    {
        $request->validate([
            'NoBukti' => 'required',
            'NoFaktur' => 'required',
            'kode' => 'required',
            'NoMsk' => 'nullable',
            'Debet' => 'required|gt:0',
            'Catatan' => 'required|string',
            'Tanggal' => 'required|date',
            'perkiraan' => 'required',
        ]);
        $NoBukti = $request->NoBukti;
        $NoFaktur = $request->NoFaktur;
        $Tanggal = $request->Tanggal;
        $KodeCustSupp = $request->kode;
        $deleteAll = $request->deleteAll;
        $NoMsk = $request->NoMsk;

        $Debet = $request->Debet;
        $Catatan = $request->Catatan;
        $perkiraan = $request->perkiraan;
        $Tipe = $request->KodePerkiraan ?? 'HT';

        DB::beginTransaction();
        try {

            $userid = auth()->user()->USERID;
            $transaksi = null;
            if ($NoMsk != null) {
                $transaksi = $this->queryModel('dbtransaksi')->where('NoBukti', $NoBukti)->where('Urut', $NoMsk)->first();
                $NoMsk = $transaksi == null ? 1 : $transaksi->Urut;
            } else {
                $transaksi = $this->queryModel('dbtransaksi')->where('NoBukti', $NoBukti)->orderBy('Urut', 'DESC')->first();
                $NoMsk = $transaksi == null ? 1 : $transaksi->Urut + 1;
            }
            $hutpiut = $this->queryModel('dbtemphutpiut')->where('NoFaktur', $NoFaktur)->where('KodeCustSupp', $KodeCustSupp)->where('Tipe', $Tipe)->orderBy('Urut', 'DESC')->get();
            if (count($hutpiut) > 0) {
                DB::statement("exec sp_TempHutPiut ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?", [
                    'I', $NoFaktur, $hutpiut[0]->NoRetur, 'L', $KodeCustSupp, $NoBukti, $NoMsk, count($hutpiut) + 1,
                    $Tanggal, $hutpiut[0]->JatuhTempo, $Debet, 0, $hutpiut[0]->Valas, $hutpiut[0]->Kurs, $hutpiut[0]->KodeSales,
                    $Tipe, $perkiraan, $Catatan, $userid, 'D', $hutpiut[0]->NoInvoice, $hutpiut[0]->KodeVls_, $hutpiut[0]->Kurs_,
                    $hutpiut[0]->KursBayar, $hutpiut[0]->DebetD, $hutpiut[0]->KreditD
                ]);
            }
            DB::commit();
            return true;
        } catch (QueryException $ex) {
            DB::rollBack();
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }

    public function hapusPelunasan($request)
    {
        $NoBukti = $request->NoBukti;
        $NoFaktur = $request->NoFaktur;
        $KodeCustSupp = $request->kode;
        $deleteAll = $request->deleteAll;
        $NoMsk = $request->NoMsk;
        $Urut = $request->Urut;

        if ($NoMsk == null) {
            $transaksi = $this->queryModel('dbtransaksi')->where('NoBukti', $NoBukti)->orderBy('Urut', 'DESC')->get();
            $NoMsk = count($transaksi) + 1;
        }
        DB::beginTransaction();
        try {
            if ($deleteAll == 'true') {
                $this->queryModel('dbhutpiut')->where('NoBukti', $NoBukti)->where('KodeCustSupp', $KodeCustSupp)->where('NoMsk', $NoMsk)->delete();
                $this->queryModel('dbtemphutpiut')->where('NoBukti', $NoBukti)->where('KodeCustSupp', $KodeCustSupp)->where('NoMsk', $NoMsk)->delete();
            } else {
                // dd($NoBukti, $NoFaktur, $KodeCustSupp, $Urut);
                $this->queryModel('dbhutpiut')->where('NoBukti', $NoBukti)->where('NoFaktur', $NoFaktur)->where('KodeCustSupp', $KodeCustSupp)->where('Urut', $Urut)->delete();
                $this->queryModel('dbtemphutpiut')->where('NoBukti', $NoBukti)->where('NoFaktur', $NoFaktur)->where('KodeCustSupp', $KodeCustSupp)->where('Urut', $Urut)->delete();
            }
            DB::commit();
            return true;
        } catch (QueryException $ex) {
            DB::rollBack();
            return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
        }
    }
    
}
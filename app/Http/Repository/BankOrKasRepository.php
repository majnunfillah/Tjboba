<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\BankOrKasInterface;
use App\Models\DBTEMPHUTPIUT;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankOrKasRepository extends BaseRepository implements BankOrKasInterface
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getAllBankOrKas()
  {
    try {
      $userid = auth()->user()->USERID;
      $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
      return DB::select("select A.NoBukti, A.Tanggal, A.Note, '' Devisi, '' Perkiraan, TipeTransHd, PerkiraanHd,
        sum(case when B.Valas='IDR' then 0.00 else B.Debet+B.Kredit end) TotalD,
        sum((B.Debet+B.Kredit)*B.Kurs) TotalRp,
        A.IsOtorisasi1, A.OtoUser1, A.TglOto1, A.IsOtorisasi2, A.OtoUser2, A.TglOto2, 
        A.IsOtorisasi3, A.OtoUser3, A.TglOto3, A.IsOtorisasi4, A.OtoUser4, A.TglOto4,
        A.IsOtorisasi5, A.OtoUser5, A.TglOto5 , sum(MaxOL) maxoto,
        Case when A.IsOtorisasi1=1 then 1 else 0 end+
                              Case when A.IsOtorisasi2=1 then 1 else 0 end+
                              Case when A.IsOtorisasi3=1 then 1 else 0 end+
                              Case when A.IsOtorisasi4=1 then 1 else 0 end+
                              Case when A.IsOtorisasi5=1 then 1 else 0 end jmloto,
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
                and A.TipeTransHd in ('BBK','BBM','BKK','BKM')
        group by A.NoBukti, A.Tanggal, A.Note, A.IsOtorisasi1, A.OtoUser1, A.TglOto1, A.IsOtorisasi2, A.OtoUser2, A.TglOto2,
        A.IsOtorisasi3, A.OtoUser3, A.TglOto3, A.IsOtorisasi4, A.OtoUser4, A.TglOto4,
        A.IsOtorisasi5, A.OtoUser5, A.TglOto5, TipeTransHd, PerkiraanHd,
                Cast(Case when Case when A.IsOtorisasi1=1 then 1 else 0 end+
                               Case when A.IsOtorisasi2=1 then 1 else 0 end+
                               Case when A.IsOtorisasi3=1 then 1 else 0 end+
                               Case when A.IsOtorisasi4=1 then 1 else 0 end+
                               Case when A.IsOtorisasi5=1 then 1 else 0 end=A.MaxOL then 0
                          else 1
                     end As Bit)
        Order by A.Nobukti");
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function getKasBankByNoBukti($NoBukti)
  {
    try {
      $res =  $this->queryModel('dbtrans')->where('NoBukti', $NoBukti)
        ->join('dbperkiraan as a', 'a.Perkiraan', '=', 'dbtrans.PerkiraanHd')->firstOrNew();
      if ($res->exists) {
        // $detail = $this->queryModel('dbtransaksi')->where('NoBukti', $NoBukti)->exists();
        $res->canEdit = true;
      }
      return $res;
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function store($request)
  {
    try {
      $userid = auth()->user()->USERID;
      $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
      // dd($request->Tanggal);
      $request->validate([
        'NoBukti' => ['required', 'string', 'max:30'],
        'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
        'Note' => ['required', 'string', 'max:500'],
        'TipeTransHd' => ['required', 'in:BBK,BBM,BKK,BKM'],
        'PerkiraanHd' => ['required'],
        'Lawan' => ['required', 'same:PerkiraanHd'],
      ]);
      $NoBukti = $request->NoBukti;

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
      // $data->Lawan = $request->Lawan;
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
      return $data;
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function update($request)
  {
    try {
      $userid = auth()->user()->USERID;
      $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
      $request->validate([
        'NoBukti' => ['required', 'string', 'max:30'],
        'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
        'Note' => ['required', 'string', 'max:500'],
      ]);
      $data = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();
      if ($data) {
        $data->Tanggal = $request->Tanggal;
        $data->Note = $request->Note;
        $data->save();
        return $data;
      }

      return abort(501, 'Error : Data tidak ditemukan');
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function delete($NoBukti)
  {
    DB::beginTransaction();
    try {
      $this->queryModel('dbtransaksi')->where('NoBukti', $NoBukti)->delete();
      if (!$this->queryModel('dbtrans')->where('NoBukti', $NoBukti)->delete()) {
        return false;
      }
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function storeKasBank($request)
  {
    DB::beginTransaction();
    try {
      $userid = auth()->user()->USERID;
      // $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
      $request->validate([
        'NoBukti' => ['required', 'string', 'max:30'],
        // 'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
        'Keterangan' => ['required', 'string', 'max:8000'],
        'Valas' => ['required', 'string', 'max:15'],
        'Kurs' => ['required', 'numeric'],
        'Debet' => ['required', 'numeric'],
        'TPHC' => ['required', 'string', 'max:15'],
        'KodeBag' => ['nullable', 'string', 'max:30'],
        'Perkiraan' => ['required', 'string', 'max:25'],
      ]);

      if ($request->pelunasan != null && $request->pelunasan != $request->Debet) {
        return abort(501, 'Error : Jumlah pelunasan tidak sama dengan jumlah debet');
      }

      $trans = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();
      $count = $this->queryModel('dbtransaksi')->where('NoBukti', $request->NoBukti)->orderBy('Urut', 'desc')->firstOrNew()->Urut;
      $KodeP = '';
      $KodeL = '';

      if ($trans->TipeTransHd == 'BBK') {
        $KodeP = '';
        $KodeL = '';
      } else if ($trans->TipeTransHd == 'BKK' && $trans->TipeTransHd == 'BBM') {
        $KodeP = 'BANK';
        $KodeL = 'BANK';
      } else if ($trans->TipeTransHd == 'BKM') {
        $KodeP = '';
        $KodeL = 'DP';
      }

      $count++;
      $DebetRp = $request->Debet * $request->Kurs;
      $StatusAktivaL = $trans->TipeTransHd == 'BKM' ? 'DP-' : '';
      $KodeBag = $request->KodeBag ?? '';
      if ($request->pelunasan != null) {
        DB::statement('exec sp_TransaksiKasBank ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', array(
          'I',                        // @Choice
          $request->NoBukti,          // @NoBukti
          $trans->NOURUT,             // @NoUrut
          $trans->Tanggal,            // @Tanggal
          $trans->Note,               // @Note
          '0',                        // @Lampiran
          '01',                       // @Devisi
          $request->Perkiraan,        // @Perkiraan
          $trans->PerkiraanHd,        // @Lawan
          $request->Keterangan,       // @Keterangan
          '',                         // @Keterangan2
          $request->Debet,            // @Debet
          0,                          // @Kredit
          $request->Valas,            // @Valas
          $request->Kurs,             // @Kurs
          $DebetRp,                   // @DebetRp
          0,                          // @KreditRp
          $trans->TipeTransHd,        // @TipeTrans
          $request->TPHC,             // @TPHC
          $request->KodeCustSupp,     // @CustSuppP
          '',                         // @CustSuppL
          $count,                     // @Urut
          '',                         // @NoAktivaP
          '',                         // @NoAktivaL
          'HT-',                      // @StatusAktivaP
          $StatusAktivaL,             // @StatusAktivaL
          '-',                        // @NoBon
          $KodeBag,                   // @KodeBag
          'HT',                       // @KodeP
          $KodeL,                     // @KodeL
          '',                         // @StatusGiro
          '',                         // Simbol
          $trans->PerkiraanHd         // @PerkiraanHd
        ));
        $userid = auth()->user()->USERID;
        $this->queryModel('dbtemphutpiut')->where('KodeCustSupp', $request->KodeCustSupp)->where('IDUser', $userid)->delete();
      } else {
        DB::statement('exec sp_TransaksiKasBank ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', array(
          'I',                        // @Choice
          $request->NoBukti,          // @NoBukti
          $trans->NOURUT,             // @NoUrut
          $trans->Tanggal,            // @Tanggal
          $trans->Note,               // @Note
          '0',                        // @Lampiran
          '01',                       // @Devisi
          $request->Perkiraan,        // @Perkiraan
          $trans->PerkiraanHd,        // @Lawan
          $request->Keterangan,       // @Keterangan
          '',                         // @Keterangan2
          $request->Debet,            // @Debet
          0,                          // @Kredit
          $request->Valas,            // @Valas
          $request->Kurs,             // @Kurs
          $DebetRp,                   // @DebetRp
          0,                          // @KreditRp
          $trans->TipeTransHd,        // @TipeTrans
          $request->TPHC,             // @TPHC
          '',                         // @CustSuppP
          '',                         // @CustSuppL
          $count,                     // @Urut
          '',                         // @NoAktivaP
          '',                         // @NoAktivaL
          '',                         // @StatusAktivaP
          $StatusAktivaL,             // @StatusAktivaL
          '-',                        // @NoBon
          $KodeBag,                   // @KodeBag
          $KodeP,                     // @KodeP
          $KodeL,                     // @KodeL
          '',                         // @StatusGiro
          '',                         // Simbol
          $trans->PerkiraanHd         // @PerkiraanHd
        ));
      }
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function updateKasBank($request)
  {
    try {
      $userid = auth()->user()->USERID;
      $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
      $validated = $request->validate([
        'NoBukti' => ['required', 'string', 'max:30'],
        // 'Tanggal' => ['required', 'date', 'after_or_equal:date(' . $periode->TAHUN . '-' . $periode->BULAN . '-01)'],
        'Keterangan' => ['required', 'string', 'max:8000'],
        'Valas' => ['required', 'string', 'max:15'],
        'Kurs' => ['required', 'numeric'],
        'Debet' => ['required', 'numeric'],
        'TPHC' => ['required', 'string', 'max:15'],
        'KodeBag' => ['nullable', 'string', 'max:30'],
        'Perkiraan' => ['required', 'string', 'max:25'],
      ]);

      if ($request->pelunasan != null && $request->pelunasan != $request->Debet) {
        return abort(501, 'Error : Jumlah pelunasan tidak sama dengan jumlah debet');
      }

      DB::beginTransaction();
      $trans = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();
      $transaksi = $this->queryModel('dbtransaksi')->where('NoBukti', $request->NoBukti)->where('Urut', $request->Urut)->first();
      if ($transaksi == null) {
        return abort(501, 'Error : Data tidak ditemukan');
      }
      $DebetRp = $request->Debet * $request->Kurs;
      if ($request->pelunasan != null) {
        DB::statement('exec sp_TransaksiKasBank ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', array(
          'U',                        // @Choice
          $request->NoBukti,          // @NoBukti
          $trans->NOURUT,             // @NoUrut
          $trans->Tanggal,            // @Tanggal
          $trans->Note,               // @Note
          $transaksi->Lampiran,       // @Lampiran
          $transaksi->Devisi,         // @Devisi
          $request->Perkiraan,        // @Perkiraan
          $trans->PerkiraanHd,        // @Lawan
          $request->Keterangan,       // @Keterangan
          $transaksi->Keterangan2,    // @Keterangan2
          $request->Debet,            // @Debet
          $transaksi->Kredit,         // @Kredit
          $request->Valas,            // @Valas
          $request->Kurs,             // @Kurs
          $DebetRp,                   // @DebetRp
          $transaksi->KreditRp,       // @KreditRp
          $trans->TipeTransHd,        // @TipeTrans
          $request->TPHC,             // @TPHC
          $request->KodeCustSupp,     // @CustSuppP
          $transaksi->CustSuppL,      // @CustSuppL
          $transaksi->Urut,           // @Urut
          $transaksi->NoAktivaP,      // @NoAktivaP
          $transaksi->NoAktivaL,      // @NoAktivaL
          'HT-',                      // @StatusAktivaP
          $transaksi->StatusAktivaL,  // @StatusAktivaL
          '-',                        // @NoBon
          $transaksi->KodeBag,        // @KodeBag
          'HT',                       // @KodeP
          $transaksi->KodeL,          // @KodeL
          $transaksi->StatusGiro,     // @StatusGiro
          $transaksi->Simbol,         // Simbol
          $trans->PerkiraanHd         // @PerkiraanHd
        ));
        $userid = auth()->user()->USERID;
        $this->queryModel('dbtemphutpiut')->where('KodeCustSupp', $request->KodeCustSupp)->where('IDUser', $userid)->delete();
      } else {
        dd('asdaasdasdsd');

        DB::statement('exec sp_TransaksiKasBank ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', array(
          'U',                        // @Choice
          $request->NoBukti,          // @NoBukti
          $trans->NOURUT,             // @NoUrut
          $trans->Tanggal,            // @Tanggal
          $trans->Note,               // @Note
          $transaksi->Lampiran,       // @Lampiran
          $transaksi->Devisi,         // @Devisi
          $request->Perkiraan,        // @Perkiraan
          $trans->PerkiraanHd,        // @Lawan
          $request->Keterangan,       // @Keterangan
          $transaksi->Keterangan2,    // @Keterangan2
          $request->Debet,            // @Debet
          $transaksi->Kredit,         // @Kredit
          $request->Valas,            // @Valas
          $request->Kurs,             // @Kurs
          $DebetRp,                   // @DebetRp
          $transaksi->KreditRp,       // @KreditRp
          $trans->TipeTransHd,        // @TipeTrans
          $request->TPHC,             // @TPHC
          $transaksi->CustSuppP,      // @CustSuppP
          $transaksi->CustSuppL,      // @CustSuppL
          $transaksi->Urut,           // @Urut
          $transaksi->NoAktivaP,      // @NoAktivaP
          $transaksi->NoAktivaL,      // @NoAktivaL
          '',                         // @StatusAktivaP
          $transaksi->StatusAktivaL,  // @StatusAktivaL
          $transaksi->NoBon,          // @NoBon
          $transaksi->KodeBag,        // @KodeBag
          '',                         // @KodeP
          $transaksi->KodeL,          // @KodeL
          $transaksi->StatusGiro,     // @StatusGiro
          $transaksi->Simbol,         // Simbol
          $trans->PerkiraanHd         // @PerkiraanHd
        ));
      }

      // DB::statement('exec sp_TransaksiKasBank ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ', array(
      //   'U',
      //   $transaksi->NoBukti,
      //   $trans->NOURUT,
      //   $trans->Tanggal,
      //   $trans->Note,
      //   $transaksi->Lampiran,
      //   $transaksi->Devisi,
      //   $transaksi->Perkiraan,
      //   $trans->PerkiraanHd,
      //   $request->Keterangan,
      //   $transaksi->Keterangan2,
      //   $request->Debet,
      //   $transaksi->Kredit,
      //   $request->Valas,
      //   $request->Kurs,
      //   $request->Debet * $request->Kurs,
      //   $transaksi->KreditRp,
      //   $trans->TipeTransHd,
      //   $request->TPHC,
      //   $transaksi->CustSuppP,
      //   $transaksi->CustSuppL,
      //   $transaksi->Urut,
      //   $transaksi->NoAktivaP,
      //   $transaksi->NoAktivaL,
      //   $transaksi->StatusAktivaP,
      //   $transaksi->StatusAktivaL,
      //   $transaksi->NoBon ?? '',
      //   $transaksi->KodeBag,
      //   $transaksi->KodeP,
      //   $transaksi->KodeL,
      //   $transaksi->StatusGiro,
      //   $transaksi->Simbol,
      //   $trans->PerkiraanHd
      // ));
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function deleteKasBank($NoBukti, $urut)
  {
    DB::beginTransaction();
    try {
      if (!DB::delete('DELETE FROM dbtransaksi WHERE NoBukti = ? AND Urut = ?', [$NoBukti, $urut])) {
        return false;
      }
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }


  public function getKasBankDetailByNoBukti($NoBukti)
  {
    try {
      return $this->queryModel('dbtransaksi')
        // when kurs greater than 1, then Debet times Kurs
        ->selectRaw("dbtransaksi.Perkiraan, Lawan, dbtransaksi.Keterangan, TPHC, a.Valas, Kurs, NoBukti, Tanggal, Urut,
        CASE WHEN Kurs > 1 THEN Debet ELSE 0 END AS Debet, DebetRp, KreditRp,
        CASE WHEN TPHC = 'C' THEN '[C]Cash' WHEN TPHC = 'T' THEN '[T]Transfer' WHEN TPHC = 'H' THEN '[H]Hutang Giro' WHEN TPHC = 'P' THEN '[P]Piutang Giro' END AS TPHC,
        a.Keterangan as KeteranganPerkiraan")
        ->join('dbperkiraan as a', 'a.Perkiraan', '=', 'dbtransaksi.Perkiraan')
        ->where('NoBukti', $NoBukti)->orderBy('Urut', 'ASC')->get();
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function setOtorisasi($request)
  {
    DB::beginTransaction();
    try {
      $userid = auth()->user()->USERID;
      $trans = $this->queryModel('dbtrans')->where('NoBukti', $request->NoBukti)->first();

      if ($request->otoLevel == 'IsOtorisasi2') {
        if (!$trans->IsOtorisasi1) {
          return abort(501, 'Error : Otorisasi 1 belum di setujui');
        }

        if ($trans->OtoUser1 === $userid) {
          return abort(501, 'Error : Otorisasi 1 tidak boleh sama dengan otorisasi 2');
        }
        if ($request->status == 1) {
          $trans->IsOtorisasi2 = 1;
          $trans->OtoUser2 = $userid;
          $trans->TglOto2 = date('Y-m-d H:i:s');
        } else {
          $trans->IsOtorisasi2 = 0;
          $trans->OtoUser2 = '';
          $trans->TglOto2 = null;
        }
      } else if ($request->otoLevel == 'IsOtorisasi1') {
        if ($trans->Otorisasi2 == 1) {
          return abort(501, 'Error : Otorisasi 2 sudah di setujui, Anda menghubungi Otorisasi 2');
        }

        if ($request->status == 1) {
          $trans->IsOtorisasi1 = 1;
          $trans->OtoUser1 = $userid;
          $trans->TglOto1 = date('Y-m-d H:i:s');
        } else {
          $trans->IsOtorisasi1 = 0;
          $trans->OtoUser1 = '';
          $trans->TglOto1 = null;
        }
      }

      $trans->save();
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function getNomorBukti($tipe)
  {
    try {
      $periode = $this->queryModel('dbperiode')->where('USERID', auth()->user()->USERID)->first();
      if ($periode == null) {
        return abort(501, 'Error : Periode belum di set');
      }

      $generate = $this->queryModel('dbnomor')->generateNoBukti($periode);
      $NoBukti = $generate[0];
      $reset = $generate[1];


      if ($reset == 'Tahun') {
        $wherePeriode = "WHERE year(Tanggal) = $periode->TAHUN";
      } else if ($reset == 'Bulan') {
        $wherePeriode = " AND year(Tanggal) = $periode->TAHUN AND month(Tanggal) = $periode->BULAN";
      }

      if ($tipe == 'BKK' || $tipe == 'BKM') {
        $wherTipe = " AND (TipeTransHd = 'BKK' OR TipeTransHd = 'BKM')";
      } else if ($tipe == 'BBK' || $tipe == 'BBM') {
        $wherTipe = " AND (TipeTransHd = 'BBK' OR TipeTransHd = 'BBM')";
      }

      $query = "SELECT TOP 1 Nobukti, TipeTransHd, Tanggal, NOURUT FROM dbtrans $wherePeriode $wherTipe ORDER BY NOURUT DESC";

      $trans = DB::select($query);

      if (count($trans) > 0) {
        $trans = $trans[0];
        $NoUrut = intval($trans->NOURUT) + 1;
      } else {
        $NoUrut = 1;
      }

      if (strlen($NoUrut) == 1) {
        $NoUrut = '0000' . $NoUrut;
      } else if (strlen($NoUrut) == 2) {
        $NoUrut = '000' . $NoUrut;
      } else if (strlen($NoUrut) == 3) {
        $NoUrut = '00' . $NoUrut;
      } else if (strlen($NoUrut) == 4) {
        $NoUrut = '0' . $NoUrut;
      }

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

  public function getDetailKasBankByNoBukti($NoBukti, $Tanggal, $Urut)
  {
    try {
      $data = $this->queryModel('dbtransaksi')
        ->select('dbtransaksi.*', 'a.Keterangan as KeteranganPerkiraan', 'c.NamaCustSupp')
        ->where('NoBukti', $NoBukti)->whereDate('dbtransaksi.Tanggal', $Tanggal)->where('Urut', $Urut)
        ->join('dbperkiraan as a', 'a.Perkiraan', '=', 'dbtransaksi.Perkiraan')
        ->leftjoin('dbcustsupp as c', function($q){
          $q->on('c.KODECUSTSUPP', '=', 'dbtransaksi.CustSuppP')
          ->select('c.NamaCustSupp'); 
        })->firstOrNew();
      
      if ($data->NoBukti != NULL) {
        $data->Kurs = number_format($data->Kurs, 2);
        $data->Debet = number_format($data->Debet, 2);
      }
      return $data;
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

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

<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GlobalHelper
{
    // Konstanta dari MyProcedure.pas
    const HRF_ANGKA = [
        '', 'Satu ', 'Dua ', 'Tiga ', 'Empat ', 'Lima ', 'Enam ', 'Tujuh ', 'Delapan ',
        'Sembilan ', 'Sepuluh ', 'Sebelas ', 'Dua Belas ', 'Tiga Belas ', 'Empat Belas ',
        'Lima Belas ', 'Enam Belas ', 'Tujuh Belas ', 'Delapan Belas ', 'Sembilan Belas '
    ];

    // Fungsi-fungsi dari MyProcedure.pas
    public static function bukaSatuanBrg($kodeBrg)
    {
        return DB::connection('stock')
            ->table('vwSatuanBrg')
            ->select('KodeBrg', 'NamaBrg', DB::raw('0 as NoSat'), 'Sat1 as Satuan', 'Isi2 as Isi', 'Sat2 as SatuanRoll')
            ->where('KodeBrg', $kodeBrg)
            ->orderBy('NoSat')
            ->get();
    }

    public static function updateValas($valas, $nilaiKurs)
    {
        return DB::connection('stock')
            ->table('dbValas')
            ->where('KodeVls', $valas)
            ->update(['Kurs' => $nilaiKurs]);
    }

    public static function checkNomor($bulan, $tahun, $tipe, $date)
    {
        $reset = config('app.global_vars.xReset', 0);
        $digitNomor = config('app.global_vars.xDigitNomor', '00000');
        
        $query = DB::connection('stock')
            ->table('dbTransaksi')
            ->select(DB::raw('ISNULL(MAX(CAST(NoUrut AS INT)), 0) as nomor'))
            ->whereRaw('MONTH(Tanggal) = ? AND YEAR(Tanggal) = ?', [$bulan, $tahun])
            ->whereRaw('ISNUMERIC(NoUrut) = 1');
            
        if (in_array($tipe, ['SPK', 'INV', 'DN', 'KN', 'HT', 'PT'])) {
            $query->whereRaw('ISNUMERIC(RIGHT(NoBukti, 4)) = 1');
        }
        
        $result = $query->first();
        $nomor = $result ? $result->nomor + 1 : 1;
        
        return [
            'nomor' => $nomor,
            'noUrut' => str_pad($nomor, strlen($digitNomor), '0', STR_PAD_LEFT)
        ];
    }

    public static function konversiKeTeks($bilangan)
    {
        if ($bilangan == 0) return 'Nol';
        
        $hasil = '';
        $ratusan = floor($bilangan / 100);
        $puluhan = floor(($bilangan % 100) / 10);
        $satuan = $bilangan % 10;
        
        if ($ratusan > 0) {
            $hasil .= self::HRF_ANGKA[$ratusan] . 'Ratus ';
        }
        
        if ($puluhan > 0) {
            if ($puluhan == 1) {
                $hasil .= self::HRF_ANGKA[$bilangan % 100];
            } else {
                $hasil .= self::HRF_ANGKA[$puluhan] . 'Puluh ';
                if ($satuan > 0) {
                    $hasil .= self::HRF_ANGKA[$satuan];
                }
            }
        } elseif ($satuan > 0) {
            $hasil .= self::HRF_ANGKA[$satuan];
        }
        
        return trim($hasil);
    }

    public static function encrypt($inString, $startKey, $multKey, $addKey)
    {
        $result = '';
        for ($i = 0; $i < strlen($inString); $i++) {
            $char = ord($inString[$i]);
            $key = ($startKey + $i) * $multKey + $addKey;
            $result .= chr($char ^ $key);
        }
        return base64_encode($result);
    }

    public static function decrypt($inString, $startKey, $multKey, $addKey)
    {
        $inString = base64_decode($inString);
        $result = '';
        for ($i = 0; $i < strlen($inString); $i++) {
            $char = ord($inString[$i]);
            $key = ($startKey + $i) * $multKey + $addKey;
            $result .= chr($char ^ $key);
        }
        return $result;
    }

    public static function generateCode($tabel, $field, $nama)
    {
        $maxCode = DB::connection('stock')
            ->table($tabel)
            ->select(DB::raw("MAX(CASE WHEN ISNUMERIC(SUBSTRING($field, 2, 5)) = 1 THEN CAST(SUBSTRING($field, 2, 5) AS INT) ELSE 0 END) as max_code"))
            ->whereRaw("SUBSTRING($field, 1, 1) = ?", [$nama])
            ->first();
            
        $nextNumber = ($maxCode->max_code ?? 0) + 1;
        return $nama . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public static function loggingData($pemakai, $aktivitas, $sumber, $noBukti, $keterangan)
    {
        return DB::connection('stock')
            ->table('dbLogFile')
            ->insert([
                'Tahun' => date('Y'),
                'Bulan' => date('n'),
                'Tanggal' => now(),
                'Pemakai' => $pemakai,
                'Aktivitas' => $aktivitas,
                'Sumber' => $sumber,
                'NoBukti' => $noBukti,
                'Keterangan' => $keterangan
            ]);
    }

    // Message functions
    public static function msgDataTidakBolehKosong($data)
    {
        return "$data tidak boleh kosong, harus diisi!";
    }

    public static function msgKoreksiDataKosong()
    {
        return "Data kosong, tidak dapat dikoreksi!";
    }

    public static function msgHapusDataKosong()
    {
        return "Data kosong, tidak dapat dihapus!";
    }

    public static function msgTidakBerhakTambahData()
    {
        return "Anda tidak berhak menambah data!";
    }

    public static function msgTidakBerhakKoreksiData()
    {
        return "Anda tidak berhak mengkoreksi data!";
    }

    public static function msgTidakBerhakHapusData()
    {
        return "Anda tidak berhak menghapus data!";
    }

    public static function msgPeriodeSudahDikunci()
    {
        return "Periode sudah dikunci!";
    }

    public static function msgTanggalTidakSesuaiPeriode()
    {
        return "Tanggal dan Periode tidak sesuai!";
    }

    public static function msgDataSudahADA($data)
    {
        return "Transaksi Nomor: $data Sudah Ada!";
    }

    public static function msgNeedOtorisasi()
    {
        return "Transaksi telah di Otorisasi, tidak dapat dikoreksi!";
    }

    public static function msgCetakOtorisasi()
    {
        return "SO Belum Di Otorisasi!";
    }

    public static function msgBlmOtorisasi()
    {
        return "Transaksi Belum di Otorisasi!";
    }

    public static function msgStatusCetak()
    {
        return "Data Sudah dicetak, Otorisasi tidak bisa dibatalkan!";
    }

    public static function msgProsesGagal($choice)
    {
        return "Proses Gagal!";
    }

    public static function msgTidakBerhakExportData()
    {
        return "Anda tidak berhak mengeksport data ke Excel!";
    }

    public static function msgTidakBerhakCetakData()
    {
        return "Anda tidak berhak mencetak data!";
    }

    public static function msgTglKirimDataKosong()
    {
        return "Data kosong, tidak dapat isi Tanggal Kirim!";
    }

    public static function msgTglTidakSesuaiPeriode()
    {
        return "Tanggal tidak sesuai dengan periode transaksi";
    }

    public static function msgNomorBuktiSudahAda($data, $noBuktiLama, $noBuktiBaru)
    {
        return "Nomor Bukti $noBuktiLama sudah ada\nSimpan data dengan nomor baru $noBuktiBaru?";
    }

    // Utility functions
    public static function myRomawi($x)
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V',
            6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
            11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$x] ?? '';
    }

    public static function myMonth($x)
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'Nopember', 12 => 'Desember'
        ];
        return $bulan[$x] ?? '';
    }

    public static function noToStr($no, $digit)
    {
        return str_pad($no, $digit, '0', STR_PAD_LEFT);
    }

    public static function cariKoma($nilai)
    {
        return strpos($nilai, ',');
    }

    public static function getMyHostName()
    {
        return gethostname();
    }

    public static function getIPFromHost($hostName)
    {
        $ip = gethostbyname($hostName);
        return $ip !== $hostName ? $ip : false;
    }

    public static function unixTimeToDateTime($unixTime)
    {
        return Carbon::createFromTimestamp($unixTime);
    }

    public static function delay($lama)
    {
        usleep($lama * 1000); // Convert to microseconds
    }

    public static function isLockPeriode($bulan, $tahun)
    {
        return DB::connection('stock')
            ->table('dbLockPeriode')
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun)
            ->exists();
    }

    public static function isLockPeriodeNK($bulan, $tahun)
    {
        return self::isLockPeriode($bulan, $tahun);
    }

    public static function cekPemakaiGdg($pemakai, $gudang)
    {
        $pemakaiAllGdg = config('app.global_vars.gPemakaiAllGdg', false);
        
        if (!$pemakaiAllGdg) {
            return DB::connection('stock')
                ->table('dbPemakaiGdg')
                ->where('UserID', $pemakai)
                ->where('KodeGdg', $gudang)
                ->exists();
        }
        
        return true;
    }

    public static function cekOtoritasMenu($pemakai, $noMenu)
    {
        $result = DB::connection('stock')
            ->table('dbflmenu as a')
            ->leftJoin('dbmenu as b', 'b.kodemenu', '=', 'a.L1')
            ->select('a.*')
            ->where('a.userid', $pemakai)
            ->where('b.ACCESS', $noMenu)
            ->first();

        return [
            'tmb' => $result->isTambah ?? false,
            'hps' => $result->ishapus ?? false,
            'krs' => $result->isKoreksi ?? false,
            'ctk' => $result->isCetak ?? false,
            'exc' => $result->isExPort ?? false,
        ];
    }

    public static function updateStatusUser($pemakai, $status)
    {
        return DB::connection('stock')
            ->table('dbPemakai')
            ->where('UserID', $pemakai)
            ->update(['Status' => $status]);
    }

    public static function hapusDaftarNomorUser($tipe, $pemakai)
    {
        return DB::connection('stock')
            ->table('dbNOMORPK')
            ->where('Tipe', $tipe)
            ->where('Pemakai', $pemakai)
            ->delete();
    }

    public static function hapusDaftarNomorGrp($tipe)
    {
        return DB::connection('stock')
            ->table('dbNOMORPK')
            ->where('Tipe', $tipe)
            ->delete();
    }

    public static function disableEnableTrigger($enabled, $table)
    {
        $action = $enabled ? 'ENABLE' : 'DISABLE';
        return DB::connection('stock')
            ->statement("ALTER TABLE dbo.$table $action TRIGGER ALL");
    }

    public static function updateUrutTransaksi($table, $noBukti)
    {
        $records = DB::connection('stock')
            ->table($table)
            ->where('nobukti', $noBukti)
            ->orderBy('urut')
            ->get();

        foreach ($records as $index => $record) {
            DB::connection('stock')
                ->table($table)
                ->where('nobukti', $record->nobukti)
                ->where('urut', $record->urut)
                ->update(['UrutTrans' => $index + 1]);
        }
    }

    public static function urutAktiva($no, $devisi, $digit)
    {
        $lastRecord = DB::connection('stock')
            ->table('dbaktiva')
            ->where('devisi', $devisi)
            ->where('Nomuka', $no)
            ->orderBy('perkiraan')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) $lastRecord->NoBelakang;
            $nextNumber = $lastNumber + 1;
            return str_pad($nextNumber, $digit, '0', STR_PAD_LEFT);
        }

        return str_pad(1, $digit, '0', STR_PAD_LEFT);
    }

    public static function urutAktiva2($no, $devisi, $digit)
    {
        $lastRecord = DB::connection('stock')
            ->table('dbaktiva')
            ->where('devisi', $devisi)
            ->whereRaw("Nomuka + '.' + NoBelakang = ?", [$no])
            ->where('Kelompok', 1)
            ->orderBy('perkiraan')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) $lastRecord->NoBelakang2;
            $nextNumber = $lastNumber + 1;
            return str_pad($nextNumber, $digit, '0', STR_PAD_LEFT);
        }

        return str_pad(1, $digit, '0', STR_PAD_LEFT);
    }

    public static function generateCode2($tabel, $field, $nama)
    {
        return self::generateCode($tabel, $field, $nama);
    }

    public static function generateCode3($tabel, $field, $nama)
    {
        return self::generateCode($tabel, $field, $nama);
    }

    public static function generateCode4($tabel, $field, $nama)
    {
        return self::generateCode($tabel, $field, $nama);
    }

    public static function generateCodeCust($tabel, $field, $nama)
    {
        return self::generateCode($tabel, $field, $nama);
    }

    public static function generateCodeSupp($tabel, $field, $nama)
    {
        return self::generateCode($tabel, $field, $nama);
    }

    public static function getImageLinkTimeStamp($fileName)
    {
        return file_exists($fileName) ? filemtime($fileName) : 0;
    }

    public static function geserKalimat($kalimat)
    {
        return $kalimat;
    }

    public static function kalimat($digit, $kata)
    {
        return str_pad($kata, $digit, ' ', STR_PAD_RIGHT);
    }

    public static function newNo($no, $digit)
    {
        return str_pad($no, $digit, '0', STR_PAD_LEFT);
    }

    public static function daftarNomor($tipe, $noUrut, $nomor, $koreksi)
    {
        return DB::connection('stock')
            ->table('dbNOMORPK')
            ->insert([
                'Tipe' => $tipe,
                'NoUrut' => $noUrut,
                'Nomor' => $nomor,
                'Koreksi' => $koreksi,
                'Tanggal' => now()
            ]);
    }

    public static function updateNomor($bulan, $tahun, $tipe, $ppn, $nomor, $digit)
    {
        return DB::connection('stock')
            ->table('dbNOMORPK')
            ->where('Tipe', $tipe)
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun)
            ->update([
                'Nomor' => $nomor,
                'Digit' => $digit,
                'PPN' => $ppn
            ]);
    }

    public static function hapusDaftarNomor($tipe, $noBukti, $pemakai)
    {
        return DB::connection('stock')
            ->table('dbNOMORPK')
            ->where('Tipe', $tipe)
            ->where('NoBukti', $noBukti)
            ->where('Pemakai', $pemakai)
            ->delete();
    }

    public static function romawi($tanggal, $mode)
    {
        $month = Carbon::parse($tanggal)->month;
        return self::myRomawi($month);
    }

    public static function cekPeriode($nama, $tgl)
    {
        $bulan = Carbon::parse($tgl)->month;
        $tahun = Carbon::parse($tgl)->year;
        
        return DB::connection('stock')
            ->table('dbLockPeriode')
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun)
            ->doesntExist();
    }

    public static function dataBersyarat($select, $params, &$query)
    {
        $query = DB::connection('stock')->select($select, $params);
        return !empty($query);
    }

    public static function dataBuka($select, &$query)
    {
        $query = DB::connection('stock')->select($select);
        return !empty($query);
    }

    public static function urutField($select, &$query)
    {
        $query = DB::connection('stock')->select($select);
        return !empty($query);
    }

    public static function urutField2($select, $params, &$query)
    {
        $query = DB::connection('stock')->select($select, $params);
        return !empty($query);
    }

    public static function myFindField($tabel, $field, $data)
    {
        return DB::connection('stock')
            ->table($tabel)
            ->where($field, $data)
            ->exists();
    }

    public static function findDuaKategori($query, $field1, $field2, $tabel, $data)
    {
        return DB::connection('stock')
            ->table($tabel)
            ->where($field1, $data)
            ->orWhere($field2, $data)
            ->get();
    }

    public static function myCariPeriode($nama)
    {
        return true;
    }

    public static function myCekLockPeriode($bukti)
    {
        return false;
    }

    public static function myAktifTgl($t, $nama)
    {
        return Carbon::parse($t);
    }

    public static function myCariUserName($nama, $kunci, &$status, &$level)
    {
        $user = DB::connection('stock')
            ->table('dbPemakai')
            ->where('UserID', $nama)
            ->where('Password', $kunci)
            ->first();

        if ($user) {
            $status = $user->Status ?? 0;
            $level = $user->Level ?? 0;
            return true;
        }

        return false;
    }

    public static function isiSatuanBrg($kodeBrg, &$namaSatuan, &$isiSatuan)
    {
        $barang = DB::connection('stock')
            ->table('dbBarang')
            ->select('Sat1', 'Sat2', 'Sat3', 'Isi1', 'Isi2', 'Isi3')
            ->where('KodeBrg', $kodeBrg)
            ->first();

        if ($barang) {
            $namaSatuan = [
                1 => $barang->Sat1,
                2 => $barang->Sat2,
                3 => $barang->Sat3
            ];
            $isiSatuan = [
                1 => $barang->Isi1,
                2 => $barang->Isi2,
                3 => $barang->Isi3
            ];
            return "[1]{$barang->Sat1} [2]{$barang->Sat2}";
        }

        return '';
    }

    public static function isiSatuanBrgJual($kodeBrg, $kodeCust, &$namaSatuan, &$isiSatuan, &$hargaBrg)
    {
        $barang = DB::connection('stock')
            ->table('dbBarang as a')
            ->leftJoin('DBBARANGCUSTOMER as b', function($join) use ($kodeCust) {
                $join->on('b.KodeBrg', '=', 'a.KODEBRG')
                     ->where('b.KodecustSupp', $kodeCust);
            })
            ->select(
                'a.Sat1', 'a.Sat2', 'a.Sat3', 'a.Isi1', 'a.Isi2', 'a.Isi3',
                DB::raw('CASE WHEN B.Harga_1 IS NULL THEN a.Hrg1_1 ELSE B.Harga_1 END as Hrg1_1'),
                DB::raw('CASE WHEN B.Harga_2 IS NULL THEN a.Hrg1_2 ELSE B.Harga_2 END as Hrg1_2'),
                'a.Hrg1_3'
            )
            ->where('a.KodeBrg', $kodeBrg)
            ->first();

        if ($barang) {
            $namaSatuan = [
                1 => $barang->Sat1,
                2 => $barang->Sat2,
                3 => $barang->Sat3
            ];
            $isiSatuan = [
                1 => $barang->Isi1,
                2 => $barang->Isi2,
                3 => $barang->Isi3
            ];
            $hargaBrg = [
                1 => $barang->Hrg1_1,
                2 => $barang->Hrg1_2,
                3 => $barang->Hrg1_3
            ];
            return "[1]{$barang->Sat1} [2]{$barang->Sat2} [3]{$barang->Sat3}";
        }

        return '';
    }

    public static function isiDataAktiva($groupAktiva, &$tipe, &$akumulasi, &$biaya1, &$biaya2, &$persen, &$pbiaya1, &$pbiaya2)
    {
        $aktiva = DB::connection('stock')
            ->table('dbposthutpiut as a')
            ->select(
                'a.*',
                DB::raw("CASE 
                    WHEN A.Tipe = 'L' THEN '[L]urus'
                    WHEN A.Tipe = 'M' THEN '[M]enurun'
                    WHEN A.Tipe = 'P' THEN '[P]ajak'
                    ELSE ''
                END as Metode")
            )
            ->where('perkiraan', $groupAktiva)
            ->where('Kode', 'AKV')
            ->first();

        if ($aktiva) {
            $tipe = $aktiva->Metode;
            $akumulasi = $aktiva->Akumulasi;
            $biaya1 = $aktiva->Biaya1;
            $biaya2 = $aktiva->Biaya2;
            $persen = $aktiva->Persen;
            $pbiaya1 = $aktiva->PersenBiaya1;
            $pbiaya2 = $aktiva->PersenBiaya2;
        }
    }

    public static function cariSatuan($kode, $noPo, $satuan, &$harga, &$isiBrg, &$sat, &$satuan, &$status)
    {
        $barang = DB::connection('stock')
            ->table('dbBarang as b')
            ->select('b.kodebrg', 'b.Sat1', 'b.isi1', 'b.Sat2', 'b.isi2')
            ->where('b.KodeBrg', $kode)
            ->orderBy('b.KodeBrg')
            ->first();

        if ($barang) {
            $satuan = $barang->Sat1;
            
            switch ($satuan) {
                case 1:
                    $sat = $barang->Sat1;
                    $isiBrg = $barang->isi2;
                    $status = true;
                    break;
                case 2:
                    $sat = $barang->Sat2;
                    $isiBrg = $barang->isi2;
                    $status = true;
                    break;
                default:
                    $status = false;
            }
        } else {
            $status = false;
        }
    }

    public static function tampilIsiData($form, &$edit, &$label, $kodeBrows, $kode, $nama)
    {
        return true;
    }

    public static function bukaNoLot($kodeGrp)
    {
        $group = DB::connection('stock')
            ->table('dbGroup')
            ->where('KodeGrp', $kodeGrp)
            ->orderBy('Kodegrp')
            ->first();

        return $group ? $group->IsiNoLot : false;
    }

    public static function tampilLabelDiskon($tipeDisc, &$lblDiscP, &$lblDiscRp)
    {
        switch ($tipeDisc) {
            case 1:
                $lblDiscP = 'Diskon %';
                $lblDiscRp = 'Diskon Rp';
                break;
            case 2:
                $lblDiscP = 'Potongan %';
                $lblDiscRp = 'Potongan Rp';
                break;
            default:
                $lblDiscP = 'Diskon %';
                $lblDiscRp = 'Diskon Rp';
        }
    }

    public static function isiQnt1FromQnt2($kodeBrg, $qnt2, &$qnt1)
    {
        $barang = DB::connection('stock')
            ->table('dbBarang')
            ->select('Isi1', 'Isi2')
            ->where('KodeBrg', $kodeBrg)
            ->first();

        if ($barang && $barang->Isi2 > 0) {
            $qnt1 = $qnt2 * $barang->Isi1 / $barang->Isi2;
        }
    }

    public static function isiComboBox($comboBox, $items)
    {
        return $items;
    }

    public static function comboBoxAutoWidth($comboBox)
    {
        return true;
    }

    public static function vlsIDR($edit, $numEdit)
    {
        return number_format($numEdit, 2, ',', '.');
    }

    public static function cekNota($field, $table, $param)
    {
        return DB::connection('stock')
            ->table($table)
            ->where($field, $param)
            ->exists();
    }

    public static function nilaiCetak($field, $table, $param)
    {
        $result = DB::connection('stock')
            ->table($table)
            ->select($field)
            ->where('id', $param)
            ->first();

        return $result ? $result->$field : '';
    }

    public static function mainCetak($frxDB1, $frxDB2, $q1, $q2, $sp1, $sp2, $nob, $fr3, $frx)
    {
        return true;
    }

    public static function mainCetak1($frxDB1, $frxDB2, $q1, $q2, $sp1, $sp2, $nob, $fr3, $frx)
    {
        return self::mainCetak($frxDB1, $frxDB2, $q1, $q2, $sp1, $sp2, $nob, $fr3, $frx);
    }

    public static function pembatalan($table, $field, $noBukti, $user, $kodeMenu)
    {
        return DB::connection('stock')
            ->table($table)
            ->where($field, $noBukti)
            ->update([
                'Status' => 'Batal',
                'UserBatal' => $user,
                'TglBatal' => now(),
                'KodeMenuBatal' => $kodeMenu
            ]);
    }

    public static function pembatalanPO($table, $field, $noBukti, $kodeBrg, $qnt, $user, $kodeMenu, $qntBatal, $urut)
    {
        return DB::connection('stock')
            ->table($table)
            ->where($field, $noBukti)
            ->where('KodeBrg', $kodeBrg)
            ->where('Qnt', $qnt)
            ->update([
                'QntBatal' => $qntBatal,
                'UrutBatal' => $urut,
                'UserBatal' => $user,
                'TglBatal' => now(),
                'KodeMenuBatal' => $kodeMenu
            ]);
    }

    public static function getBrowse($kodeBrowse, $db, $kode, $name, $form, $edit, $label)
    {
        return true;
    }

    public static function saveToIniFiles($pemakai, $form, $grid)
    {
        return true;
    }

    public static function viewOtorisasi($tableView, $query, $ol)
    {
        return true;
    }

    public static function viewOtorisasiNonBandedTableView($tableView, $query, $ol)
    {
        return self::viewOtorisasi($tableView, $query, $ol);
    }

    public static function otorisasi($table, $kodeMenu, $idUser, $kunci, $noBukti)
    {
        return true;
    }

    public static function batalOtorisasi($table, $kodeMenu, $idUser, $kunci, $noBukti)
    {
        return true;
    }

    public static function cekOtorisasi($table, $kodeMenu, $idUser, $kunci, $noBukti)
    {
        return true;
    }

    public static function getNomorJurnal($table, $kodeMenu, $idUser, $kunci, $noBukti)
    {
        return '';
    }

    public static function konfigNojurnal($tipe, $nomor, &$f1, &$f2, &$f3, &$f4, &$noUrut, &$y1, &$y2, &$y3, &$y4, $date, $ppn)
    {
        return true;
    }

    public static function konfig($tipe, $nomor, &$f1, &$f2, &$f3, &$f4, &$noUrut, &$y1, &$y2, &$y3, &$y4, $date, $isPpn = false)
    {
        return true;
    }

    public static function myNoBukti()
    {
        return '';
    }
} 
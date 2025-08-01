<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutupBukuController extends Controller
{
    /**
     * Menampilkan form input untuk proses Tutup Buku
     */
    public function showForm()
    {
        return view('tutup-buku.index', [
            'currentMonth' => date('m'),
            'currentYear' => date('Y'),
        ]);
    }

    /**
     * Proses utama Tutup Buku
     */
    public function processTutupBuku(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:1900'],
        ]);

        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        try {
            DB::beginTransaction();

            // Bagian utama proses sesuai Delphi
            $this->postingData($bulan, $tahun);
            $this->postingKacangBasah($bulan, $tahun);
            $this->jurnalKoreksi($bulan, $tahun);

            // Menyimpan periode tutup buku
            DB::table('dbperiode')->insert([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user_id' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Proses Tutup Buku berhasil diselesaikan!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menjalankan proses Tutup Buku.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logika untuk Posting Data
     */
    private function postingData($bulan, $tahun)
    {
        // Contoh logika posting data (mengambil nilai jurnal dan menyimpan)
        $jurnalNilai = DB::table('jurnal')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->sum('jumlah');

        // Simpan ke tabel lain (contoh: tabel jurnal_posting)
        DB::table('jurnal_posting')->insert([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'jumlah' => $jurnalNilai,
            'created_at' => now(),
        ]);

        // Anda dapat mengembangkan logika posting lebih jauh di sini
    }

    /**
     * Logika untuk Posting Data Kacang Basah
     */
    private function postingKacangBasah($bulan, $tahun)
    {
        // Contoh logika untuk memposting data kacang basah
        $dataBasah = DB::table('kacang_basah')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->sum('nilai');

        DB::table('kacang_posting')->insert([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nilai' => $dataBasah,
            'created_at' => now(),
        ]);
    }

    /**
     * Jurnal Koreksi (dengan Hasil Akhir)
     */
    private function jurnalKoreksi($bulan, $tahun)
    {
        // Contoh logika koreksi jurnal
        $hasilAkhir = DB::table('jurnal_koreksi_temp')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->sum('jumlah');

        if ($hasilAkhir) {
            DB::table('jurnal_koreksi')->insert([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'hasil_akhir' => $hasilAkhir,
                'created_at' => now(),
            ]);
        }
    }
}
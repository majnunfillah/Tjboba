<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Services\CustomDatatable;
use App\Http\Repository\BaseRepository;

class HitungUlangHPPController extends Controller
{
    protected $customDatatable;
    protected $baseRepository;

    public function __construct(CustomDatatable $customDatatable, BaseRepository $baseRepository)
    {
        $this->customDatatable = $customDatatable;
        $this->baseRepository = $baseRepository;
    }

    /**
     * Display the main view for Hitung Ulang HPP
     */
    public function index()
    {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        return view('accounting.hitung_ulang_hpp', compact('currentMonth', 'currentYear'));
    }

    /**
     * Get stock minus data for DataTable
     */
    public function getStockMinusData(Request $request)
    {
        $query = DB::table('TempStockMinus')
            ->where('IDUser', session('user_id'))
            ->select([
                'IDUser',
                'Urut',
                'JenisBahan',
                'KodeGdg',
                'KodeBrg',
                'KodeBng',
                'KodeJenis',
                'KodeWarna'
            ]);

        return $this->customDatatable->generate($query, $request, [
            'IDUser' => 'IDUser',
            'Urut' => 'Urut',
            'JenisBahan' => 'JenisBahan',
            'KodeGdg' => 'KodeGdg',
            'KodeBrg' => 'KodeBrg',
            'KodeBng' => 'KodeBng',
            'KodeJenis' => 'KodeJenis',
            'KodeWarna' => 'KodeWarna'
        ]);
    }

    /**
     * Main process for Hitung Ulang HPP
     */
    public function prosesHitungUlangHPP(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('n'));
            $tahun = $request->input('tahun', date('Y'));
            $jenisBarang = $request->input('jenis_barang', 'semua');
            $kodeBarangAwal = $request->input('kode_barang_awal', '');
            $kodeBarangAkhir = $request->input('kode_barang_akhir', '');

            // Validate period lock
            if ($this->isPeriodLocked($bulan, $tahun)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode sudah terkunci, tidak dapat hitung ulang'
                ]);
            }

            // Clear previous stock minus data
            DB::table('TempStockMinus')
                ->where('IDUser', session('user_id'))
                ->delete();

            // Initialize progress
            $totalItems = $this->getTotalItems($bulan, $tahun, $jenisBarang, $kodeBarangAwal, $kodeBarangAkhir);
            
            return response()->json([
                'success' => true,
                'message' => 'Memulai proses hitung ulang HPP',
                'total_items' => $totalItems,
                'process_type' => 'hitung_ulang_hpp',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'jenis_barang' => $jenisBarang,
                'kode_barang_awal' => $kodeBarangAwal,
                'kode_barang_akhir' => $kodeBarangAkhir
            ]);

        } catch (\Exception $e) {
            Log::error('Error in prosesHitungUlangHPP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute the actual HPP calculation process
     */
    public function executeHitungUlangHPP(Request $request)
    {
        try {
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            $jenisBarang = $request->input('jenis_barang');
            $kodeBarangAwal = $request->input('kode_barang_awal');
            $kodeBarangAkhir = $request->input('kode_barang_akhir');

            // Log the process
            Log::info('Memulai proses hitung ulang HPP periode ' . $bulan . '-' . $tahun);

            // Step 1: Initialize stock data
            $this->initializeStockData($bulan, $tahun, $jenisBarang, $kodeBarangAwal, $kodeBarangAkhir);

            // Step 2: Process materials (ProsesBahan)
            $this->prosesBahan($bulan, $tahun, $kodeBarangAwal, $kodeBarangAkhir);

            // Step 3: Update HPP to transactions (InHPPtoTRS)
            $this->inHPPtoTRS($bulan, $tahun, $kodeBarangAwal, $kodeBarangAkhir);

            // Step 4: Process packaging changes (ProsesKemasan)
            $this->prosesKemasan($bulan, $tahun);

            // Step 5: Process end of month (ProsesAkhirBulan)
            $this->prosesAkhirBulan($bulan, $tahun);

            return response()->json([
                'success' => true,
                'message' => 'Proses hitung ulang HPP selesai',
                'total_processed' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('Error in executeHitungUlangHPP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if period is locked
     */
    private function isPeriodLocked($bulan, $tahun)
    {
        // Implementation depends on your locking mechanism
        // This is a placeholder - implement according to your system
        return false;
    }

    /**
     * Get total items to process
     */
    private function getTotalItems($bulan, $tahun, $jenisBarang, $kodeBarangAwal, $kodeBarangAkhir)
    {
        $query = DB::table('vwKartuStock as a')
            ->leftJoin('dbBarang as b', 'b.KodeBrg', '=', 'a.KodeBrg')
            ->where('a.Tahun', $tahun)
            ->where('a.Bulan', $bulan)
            ->distinct()
            ->select('a.KodeBrg', 'b.NamaBrg');

        if ($jenisBarang === 'per_barang') {
            $query->whereBetween('a.KodeBrg', [$kodeBarangAwal, $kodeBarangAkhir]);
        }

        return $query->count();
    }

    /**
     * Initialize stock data
     */
    private function initializeStockData($bulan, $tahun, $jenisBarang, $kodeBarangAwal, $kodeBarangAkhir)
    {
        $query = DB::table('dbStockBrg')
            ->where('Bulan', $bulan)
            ->where('Tahun', $tahun);

        if ($jenisBarang === 'per_barang') {
            $query->whereBetween('KodeBrg', [$kodeBarangAwal, $kodeBarangAkhir]);
        }

        $query->update([
            'HRGPBL' => 0,
            'HRGRPB' => 0,
            'HRGPNJ' => 0,
            'HRGRPJ' => 0,
            'HRGADI' => 0,
            'HRGMADI' => 0,
            'HRGADO' => 0,
            'HRGMADO' => 0,
            'HRGUKI' => 0,
            'HRGUKO' => 0,
            'HRGTRI' => 0,
            'HRGTRO' => 0,
            'HRGPMK' => 0,
            'HRGRPK' => 0,
            'HRGHPrd' => 0
        ]);
    }

    /**
     * Process materials (equivalent to ProsesBahan in Delphi)
     */
    private function prosesBahan($bulan, $tahun, $kodeBarangAwal, $kodeBarangAkhir)
    {
        // This is a complex calculation that needs to be implemented
        // based on your specific business logic and database structure
        // The Delphi version has extensive logic for calculating average costs
        
        Log::info('Processing materials for period ' . $bulan . '-' . $tahun);
        
        // Placeholder implementation
        // You'll need to implement the full logic based on your requirements
    }

    /**
     * Update HPP to transactions (equivalent to InHPPtoTRS in Delphi)
     */
    private function inHPPtoTRS($bulan, $tahun, $kodeBarangAwal, $kodeBarangAkhir)
    {
        // This updates HPP values in various transaction tables
        // Implementation depends on your specific transaction types
        
        Log::info('Updating HPP to transactions for period ' . $bulan . '-' . $tahun);
        
        // Placeholder implementation
    }

    /**
     * Process packaging changes (equivalent to ProsesKemasan in Delphi)
     */
    private function prosesKemasan($bulan, $tahun)
    {
        // Process packaging changes for the period
        
        Log::info('Processing packaging changes for period ' . $bulan . '-' . $tahun);
        
        // Placeholder implementation
    }

    /**
     * Process end of month (equivalent to ProsesAkhirBulan in Delphi)
     */
    private function prosesAkhirBulan($bulan, $tahun)
    {
        // Calculate next period
        if ($bulan == 12) {
            $nextBulan = 1;
            $nextTahun = $tahun + 1;
        } else {
            $nextBulan = $bulan + 1;
            $nextTahun = $tahun;
        }

        // Reset initial quantities for next period
        DB::table('dbStockBrg')
            ->where('Bulan', $nextBulan)
            ->where('Tahun', $nextTahun)
            ->update([
                'QntAwal' => 0,
                'Qnt2Awal' => 0,
                'HrgAwal' => 0
            ]);

        // Transfer balances to next period
        $this->transferBalancesToNextPeriod($bulan, $tahun, $nextBulan, $nextTahun);
    }

    /**
     * Transfer balances to next period
     */
    private function transferBalancesToNextPeriod($bulan, $tahun, $nextBulan, $nextTahun)
    {
        // Get current period balances
        $balances = DB::table('dbStockBrg as b')
            ->leftJoin('dbBarang as c', 'c.KodeBrg', '=', 'b.KodeBrg')
            ->where('b.Bulan', $bulan)
            ->where('b.Tahun', $tahun)
            ->select([
                'b.KodeBrg',
                'c.NamaBrg',
                'b.KodeGdg',
                'b.SaldoQnt as QntAwal',
                'b.Saldo2Qnt as Qnt2Awal',
                'b.SaldoRp as HrgAwal',
                'b.HrgRata'
            ])
            ->get();

        foreach ($balances as $balance) {
            // Check if record exists in next period
            $exists = DB::table('dbStockBrg')
                ->where('KodeBrg', $balance->KodeBrg)
                ->where('KodeGdg', $balance->KodeGdg)
                ->where('Bulan', $nextBulan)
                ->where('Tahun', $nextTahun)
                ->exists();

            if ($exists) {
                // Update existing record
                DB::table('dbStockBrg')
                    ->where('KodeBrg', $balance->KodeBrg)
                    ->where('KodeGdg', $balance->KodeGdg)
                    ->where('Bulan', $nextBulan)
                    ->where('Tahun', $nextTahun)
                    ->update([
                        'QntAwal' => $balance->QntAwal,
                        'Qnt2Awal' => $balance->Qnt2Awal,
                        'HrgAwal' => $balance->HrgAwal,
                        'HrgRata' => $balance->HrgRata
                    ]);
            } else {
                // Insert new record
                DB::table('dbStockBrg')->insert([
                    'KodeBrg' => $balance->KodeBrg,
                    'KodeGdg' => $balance->KodeGdg,
                    'Bulan' => $nextBulan,
                    'Tahun' => $nextTahun,
                    'QntAwal' => $balance->QntAwal,
                    'Qnt2Awal' => $balance->Qnt2Awal,
                    'HrgAwal' => $balance->HrgAwal,
                    'HrgRata' => $balance->HrgRata
                ]);
            }
        }
    }

    /**
     * Export stock minus data to Excel
     */
    public function exportStockMinus(Request $request)
    {
        try {
            $data = DB::table('TempStockMinus')
                ->where('IDUser', session('user_id'))
                ->get();

            // Generate Excel file
            $filename = 'Stock_Minus_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // You can use Laravel Excel package or similar for Excel generation
            // For now, return CSV format
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['IDUser', 'Urut', 'JenisBahan', 'KodeGdg', 'KodeBrg', 'KodeBng', 'KodeJenis', 'KodeWarna']);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->IDUser,
                        $row->Urut,
                        $row->JenisBahan,
                        $row->KodeGdg,
                        $row->KodeBrg,
                        $row->KodeBng,
                        $row->KodeJenis,
                        $row->KodeWarna
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting stock minus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage()
            ]);
        }
    }
} 
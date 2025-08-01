<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TutupBukuController extends Controller
{
    /**
     * Menampilkan halaman utama Tutup Buku.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $periode = DB::table('dbperiode')
            ->select('BULAN', 'TAHUN')
            ->where('USERID', auth()->user()->USERID)
            ->first() ?? (object)[
            'BULAN' => date('n'),
            'TAHUN' => date('Y')
        ];
        
        // Return view dengan mengirim data 'periode'
        return view('utilitas.tutup-buku', [
            'periode' => $periode
        ]);
    }

    /**
     * Get bulan name (same as Delphi function)
     */
    private function getBulanName($bulan)
    {
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari', 
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $namaBulan[$bulan] ?? 'Unknown';
    }

    /**
     * Proses tutup buku berdasarkan jenis proses
     */
    public function proses(Request $request)
    {
        try {
            // Validasi dengan error handling yang lebih baik
            $validated = $request->validate([
                'bulan' => 'required|integer|between:1,12',
                'tahun' => 'required|integer|between:2000,2099',
                'jenis_proses' => 'required|integer|between:0,7'
            ]);

            $bulan = (int) $validated['bulan'];
            $tahun = (int) $validated['tahun'];
            $jenisProses = (int) $validated['jenis_proses'];

            // Update periode untuk user yang sedang login
            DB::table('dbperiode')
                ->where('USERID', auth()->user()->USERID)
                ->update([
                    'BULAN' => $bulan,
                    'TAHUN' => $tahun
                ]);

            // Proses berdasarkan jenis
            switch ($jenisProses) {
                case 0: // Semua
                    $this->prosesSemua($bulan, $tahun);
                    break;
                case 1: // Proses Aktiva
                    $aktivaResult = $this->prosesAktiva($bulan, $tahun);
                    return $aktivaResult;
                case 2: // Hitung Ulang Neraca
                    $neracaResult = $this->hitungUlangNeraca($bulan, $tahun);
                    return $neracaResult;
                case 3: // Hitung Ulang Aktiva
                    $aktivaResult = $this->hitungUlangAktiva($bulan, $tahun);
                    return $aktivaResult;
                case 4: // HPP dan Rugi Laba
                    $hppResult = $this->prosesHPPRugiLaba($bulan, $tahun);
                    return $hppResult;
                case 5: // Proses Dashboard
                    $dashboardResult = $this->prosesDashboard($bulan, $tahun);
                    return $dashboardResult;
                case 6: // Proses Aktiva Fiskal
                    $aktivaFiskalResult = $this->prosesAktivaFiskal($bulan, $tahun);
                    return $aktivaFiskalResult;
                case 7: // Hitung Ulang Aktiva Fiskal
                    $aktivaFiskalResult = $this->hitungUlangAktivaFiskal($bulan, $tahun);
                    return $aktivaFiskalResult;
                default:
                    throw new \Exception('Jenis proses tidak valid: ' . $jenisProses);
            }

            return response()->json([
                'success' => true,
                'message' => 'Proses tutup buku berhasil dilakukan untuk periode ' . $this->getBulanName($bulan) . ' ' . $tahun
            ])->header('Content-Type', 'application/json');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            Log::error('Validation failed:', $e->errors());
            
            // Flatten validation errors manually since array_flatten might not be available
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $errorMessages)
            ], 422)->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            Log::error('Error in proses tutup buku: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }



    /**
     * Get end of month date
     */
    private function getAkhirBulan($bulan, $tahun)
    {
        if ($bulan < 12) {
            $akhirBulan = Carbon::create($tahun, $bulan + 1, 1)->subDay();
        } else {
            $akhirBulan = Carbon::create($tahun, $bulan, 31);
        }
        return $akhirBulan;
    }

    /**
     * Proses Aktiva - Based on Delphi implementation
     */
    public function prosesAktiva($bulan, $tahun)
    {
        // Check if period is locked (equivalent to IsLockPeriode in Delphi)
      //  if ($this->isPeriodLocked($bulan, $tahun)) {
            // Get end of month date (same logic as Delphi)
            $akhirBulan = $this->getAkhirBulan($bulan, $tahun);
            
            // Get document number (equivalent to Check_Nomor in Delphi)
            //$nomorBukti = $this->generateDocumentNumber($bulan, $tahun, 'AKM');
            
            // Set memory and time limits
            ini_set('memory_limit', '512M');
            set_time_limit(0);
            
            DB::beginTransaction();
            
            try {
                // Disable delete trigger (same as Delphi)
                DB::statement("ALTER TABLE dbo.dbTransaksi DISABLE TRIGGER TRI_Del_DBTRANSAKSI");
                
                // Delete existing AKM transactions for the period (same as Delphi)
                DB::statement('
                    DELETE FROM dbtransaksi 
                    WHERE Nobukti LIKE \'%AKM%\' 
                    AND MONTH(tanggal) = ? 
                    AND YEAR(tanggal) = ?
                ', [$bulan, $tahun]);
                
                DB::statement("
                    DELETE FROM dbtrans 
                    WHERE Nobukti LIKE '%AKM%' 
                    AND MONTH(tanggal) = ? 
                    AND YEAR(tanggal) = ?
                ", [$bulan, $tahun]);
                
                // Disable add trigger (same as Delphi)
                DB::statement("ALTER TABLE dbo.dbTransaksi DISABLE TRIGGER TRI_ADD_DBTRANSAKSI");
                
                // Get aktiva data (same query as Delphi)
                
                $results = DB::select("
                    DECLARE @Bulan INT, @Tahun INT, @Ttgl DATETIME;
                    SELECT @Bulan = ?, @Tahun = ?, @Ttgl = ?;
                    
                    SELECT 
                        a.perkiraan,
                        a.keterangan,
                        a.persen,
                        a.tipe,
                        a.akumulasi,
                        a.biaya,
                        a.biaya2,
                        a.persenbiaya1,
                        a.persenbiaya2,
                        a.biaya3,
                        a.biaya4,
                        a.persenbiaya3,
                        a.persenbiaya4,
                        a.TipeAktiva,
                        a.kodebag,
                        A.Devisi,
                        A.Tanggal
                    FROM dbaktiva a
                    WHERE a.tanggal <= @Ttgl
                    ORDER BY A.Perkiraan
                ", [$bulan, $tahun, $akhirBulan->toDateTimeString()]);
                

                
                // Process each aktiva (same loop as Delphi)
                $totalAktiva = count($results);
                $processedCount = 0;
                
                foreach ($results as $aktiva) {
                    // Call stored procedure with exact same parameters as Delphi
                    DB::statement('EXEC ProsesAktiva ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', [
                        $bulan,                                    // @Bulan
                        $tahun,                                    // @Tahun
                        $aktiva->Devisi,                           // @Devisi
                        auth()->user()->USERID,                    // @UserID
                        $akhirBulan->toDateTimeString(),           // @Tanggal
                        $aktiva->perkiraan,                        // @KodeAktiva
                        $aktiva->kodebag,                          // @KodeBag
                        $aktiva->keterangan,                       // @Keterangan
                        $aktiva->persen,                           // @Susut
                        $aktiva->tipe,                             // @Metode
                        $aktiva->akumulasi,                        // @Akumulasi
                        $aktiva->biaya,                            // @Biaya1
                        $aktiva->persenbiaya1,                     // @Persenbiaya1
                        $aktiva->biaya2,                           // @Biaya2
                        $aktiva->persenbiaya2,                     // @Persenbiaya2
                        $aktiva->biaya3,                           // @Biaya3
                        $aktiva->persenbiaya3,                     // @Persenbiaya3
                        $aktiva->biaya4,                           // @Biaya4
                        $aktiva->persenbiaya4,                     // @Persenbiaya4
                        $aktiva->TipeAktiva,                       // @TipeAktiva
                        '00625/AKM/PWT/022022',                    // @NoBukti
                        '00625',                                   // @Nourut
                        $aktiva->Tanggal                           // @TglPerolehan
                    ]);
                    
                    $processedCount++;
                    
                    // Calculate progress percentage (30-90% range for aktiva processing)
                    $progressPercent = 30 + (($processedCount / $totalAktiva) * 60);
                    
                    // For real-time progress, you could use Server-Sent Events here
                    // For now, we'll include progress info in the final response

                }
                
                // Re-enable triggers (same as Delphi)
                DB::statement("ALTER TABLE dbo.dbTransaksi ENABLE TRIGGER TRI_ADD_DBTRANSAKSI");
                DB::statement("ALTER TABLE dbo.dbTransaksi ENABLE TRIGGER TRI_Del_DBTRANSAKSI");
                
                DB::commit();
                

                
                // Return success response
                return response()->json([
                    'success' => true,
                    'message' => 'Proses tutup buku berhasil dilakukan untuk periode ' . $this->getBulanName($bulan) . ' ' . $tahun,
                    'aktiva_processed' => $processedCount,
                    'total_aktiva' => $totalAktiva,
                    'estimated_aktiva_count' => $totalAktiva, // Untuk JavaScript progress simulation
                    'progress_info' => [
                        'total_aktiva' => $totalAktiva,
                        'processed_count' => $processedCount,
                        'progress_percent' => 100, // Final progress
                        'bulan' => $bulan,
                        'tahun' => $tahun
                    ]
                ])->header('Content-Type', 'application/json'); 
                
            } catch (\Exception $e) {
                DB::rollback();
                
                // Re-enable triggers even on error (same as Delphi)
                try {
                    DB::statement("ALTER TABLE dbo.dbTransaksi ENABLE TRIGGER TRI_ADD_DBTRANSAKSI");
                    DB::statement("ALTER TABLE dbo.dbTransaksi ENABLE TRIGGER TRI_Del_DBTRANSAKSI");
                } catch (\Exception $triggerError) {
                    Log::error("Failed to re-enable triggers", ['error' => $triggerError->getMessage()]);
                }
                
                Log::error("Proses Aktiva failed", [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw new \Exception('Proses Aktiva gagal: ' . $e->getMessage());
            }
        /*} else {
            throw new \Exception('Periode sudah di lock');
        }*/
    }

    /**
     * Get progress status for aktiva processing (for future real-time implementation)
     */
    public function getProgress($bulan, $tahun)
    {
        // This method can be used for real-time progress updates
        // Implementation would depend on your caching strategy
        return response()->json([
            'progress' => 0,
            'message' => 'Progress tracking not implemented yet'
        ]);
    }

    /**
     * Check if period is locked (equivalent to IsLockPeriode in Delphi)
     */
    private function isPeriodLocked($bulan, $tahun)
    {
        // Implementation depends on your business logic
        // This is a placeholder - implement based on your requirements
        return false; // For now, assume period is not locked
    }
    
    /**
     * Generate document number (equivalent to Check_Nomor in Delphi)
     */
    private function generateDocumentNumber($bulan, $tahun, $prefix)
    {
        // Implementation depends on your numbering system
        // This is a placeholder - implement based on your requirements
        $bulanStr = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $tahunStr = substr($tahun, -2);
        return "{$prefix}{$bulanStr}/{$tahunStr}";
    }
    
    /**
     * Generate sequence number for document
     */
    private function generateNomorUrut($nomorBukti)
    {
        // Extract sequence number from document number
        // This is a placeholder - implement based on your requirements
        return '001';
    }

    /**
     * Hitung Ulang Neraca - Based on Delphi ProsesHitUlangNeraca
     */
    private function hitungUlangNeraca($bulan, $tahun)
    {
        Log::info("Hitung Ulang Neraca started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        DB::beginTransaction();
        
        try {
            // Step 1: Reset all balance fields to 0 (same as Delphi)
            DB::statement("
                UPDATE DBNERACA 
                SET MD = 0, MK = 0, MDRp = 0, MKRp = 0, 
                    JPD = 0, JPK = 0, JPDRp = 0, JPKRp = 0, 
                    RLD = 0, RLK = 0, RLDRp = 0, RLKRp = 0
                WHERE Bulan = ? AND Tahun = ?
            ", [$bulan, $tahun]);
            
            // Step 2: Get all transactions for the period (same as Delphi)
            $transactions = DB::select("
                SELECT 
                    a.nobukti,
                    A.Devisi,
                    a.Perkiraan,
                    a.Lawan,
                    A.Valas,
                    a.Kurs,
                    a.Debet,
                    a.DebetRp,
                    b.DK AS DKP,
                    c.DK AS DKL,
                    A.StatusAktivaP,
                    a.StatusAktivaL,
                    a.NoAktivaP,
                    a.NoAktivaL,
                    a.TipeTrans
                FROM vwTransaksi a
                LEFT OUTER JOIN DBPERKIRAAN b ON b.Perkiraan = a.Perkiraan
                LEFT OUTER JOIN DBPERKIRAAN c ON c.Perkiraan = a.Lawan
                WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                ORDER BY A.nobukti
            ", [$bulan, $tahun]);
            
            $totalTransactions = count($transactions);
            Log::info("Processing {$totalTransactions} transactions for Hitung Ulang Neraca");
            
            // Step 3: Process each transaction using stored procedure (same as Delphi)
            foreach ($transactions as $index => $transaction) {
                // Log progress every 100 transactions
                if ($index % 100 === 0) {
                    Log::info("Processing transaction {$index}/{$totalTransactions}: {$transaction->nobukti}");
                }
                
                // Call stored procedure sp_HitungUlangTransaksi (same as Delphi)
                DB::statement('EXEC sp_HitungUlangTransaksi ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', [
                    $transaction->Devisi,           // @Devisi
                    $transaction->Perkiraan,        // @Perkiraan
                    $transaction->DKP,              // @DKP
                    $transaction->Lawan,            // @Lawan
                    $transaction->DKL,              // @DKL
                    $transaction->Debet,            // @Debet
                    $transaction->DebetRp,          // @DebetRp
                    $transaction->StatusAktivaP,    // @StatusAktivaP
                    $transaction->StatusAktivaL,    // @StatusAktivaL
                    $transaction->NoAktivaP,        // @NoAktivaP
                    $transaction->NoAktivaL,        // @NoAktivaL
                    $transaction->TipeTrans,        // @TipeTrans
                    $bulan,                         // @Bulan
                    $tahun,                         // @Tahun
                    $transaction->Valas             // @Valas
                ]);
            }
            
            // Step 4: Update DK field from DBPERKIRAAN (same as Delphi)
            DB::statement("
                UPDATE DBNERACA 
                SET DK = ISNULL(B.DK, 0)
                FROM DBNERACA A
                LEFT OUTER JOIN DBPERKIRAAN B ON B.Perkiraan = A.Perkiraan
                WHERE A.Tahun = ? AND A.Bulan = ?
            ", [$tahun, $bulan]);
            
            // Step 5: Get distinct accounts for balance transfer (same as Delphi)
            $accounts = DB::select("
                SELECT DISTINCT 
                    A.Perkiraan,
                    B.Keterangan,
                    A.Devisi
                FROM dbNeraca a
                LEFT OUTER JOIN DBPERKIRAAN b ON b.Perkiraan = a.Perkiraan
                WHERE Bulan = ? AND Tahun = ?
                ORDER BY A.Perkiraan
            ", [$bulan, $tahun]);
            
            $totalAccounts = count($accounts);
            Log::info("Processing {$totalAccounts} accounts for balance transfer");
            
            // Step 6: Process balance transfer for each account (same as Delphi)
            foreach ($accounts as $index => $account) {
                // Log progress every 50 accounts
                if ($index % 50 === 0) {
                    Log::info("Processing account {$index}/{$totalAccounts}: {$account->Keterangan} ({$account->Perkiraan})");
                }
                
                // Call stored procedure sp_PindahSaldoNeraca (same as Delphi)
                DB::statement('EXEC sp_PindahSaldoNeraca ?, ?, ?, ?', [
                    $account->Devisi,      // @Devisi
                    $account->Perkiraan,   // @Perkiraan
                    $bulan,                // @Bulan
                    $tahun                 // @Tahun
                ]);
            }
            
            DB::commit();
            
            Log::info("Hitung Ulang Neraca completed successfully", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'transactions_processed' => $totalTransactions,
                'accounts_processed' => $totalAccounts
            ]);
            
            // Return success response with progress info
            return response()->json([
                'success' => true,
                'message' => 'Hitung Ulang Neraca berhasil dilakukan untuk periode ' . $this->getBulanName($bulan) . ' ' . $tahun,
                'transactions_processed' => $totalTransactions,
                'total_transactions' => $totalTransactions,
                'accounts_processed' => $totalAccounts,
                'total_accounts' => $totalAccounts,
                'progress_info' => [
                    'total_transactions' => $totalTransactions,
                    'transactions_processed' => $totalTransactions,
                    'total_accounts' => $totalAccounts,
                    'accounts_processed' => $totalAccounts,
                    'progress_percent' => 100,
                    'bulan' => $bulan,
                    'tahun' => $tahun
                ]
            ])->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Hitung Ulang Neraca failed", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Hitung Ulang Neraca gagal: ' . $e->getMessage());
        }
    }

    /**
     * Hitung Ulang Aktiva
     */
    private function hitungUlangAktiva($bulan, $tahun)
    {
        Log::info("Hitung Ulang Aktiva started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        try {
            // Get aktiva records that have transactions in the period
            $aktivaRecords = DB::select("
                SELECT DISTINCT 
                    noaktivaP as perkiraan
                FROM dbtransaksi
                WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? AND noaktivaP <> ''
                UNION ALL
                SELECT DISTINCT 
                    noaktivaL as perkiraan
                FROM dbtransaksi
                WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? AND noaktivaL <> ''
                ORDER BY perkiraan
            ", [$bulan, $tahun, $bulan, $tahun]);
            
            $totalAktiva = count($aktivaRecords);
            Log::info("Processing {$totalAktiva} aktiva records for Hitung Ulang Aktiva");
            
            // Process each aktiva record
            foreach ($aktivaRecords as $index => $aktiva) {
                // Log progress every 50 records
                if ($index % 50 === 0) {
                    Log::info("Processing aktiva {$index}/{$totalAktiva}: {$aktiva->perkiraan}");
                }
                
                // Call stored procedure sp_HitungUlangAktiva
                DB::statement('EXEC sp_HitungUlangAktiva ?, ?, ?', [
                    $bulan,
                    $tahun,
                    $aktiva->perkiraan
                ]);
            }
            
            Log::info("Hitung Ulang Aktiva completed successfully", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'aktiva_processed' => $totalAktiva
            ]);
            
            // Return success response with progress info
            return response()->json([
                'success' => true,
                'message' => 'Hitung Ulang Aktiva berhasil dilakukan untuk periode ' . $this->getBulanName($bulan) . ' ' . $tahun,
                'aktiva_processed' => $totalAktiva,
                'total_aktiva' => $totalAktiva,
                'progress_info' => [
                    'total_aktiva' => $totalAktiva,
                    'processed_count' => $totalAktiva,
                    'progress_percent' => 100,
                    'bulan' => $bulan,
                    'tahun' => $tahun
                ]
            ])->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            Log::error("Hitung Ulang Aktiva failed", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Hitung Ulang Aktiva gagal: ' . $e->getMessage());
        }
    }

    /**
     * Proses HPP dan Rugi Laba
     */
    private function prosesHPPRugiLaba($bulan, $tahun)
    {
        // Implementation for HPP and Rugi Laba processing
        $this->prosesHPPRL($bulan, $tahun, true);  // HPP
        $this->prosesHPPRL($bulan, $tahun, false); // Rugi Laba
    }

    /**
     * Proses HPP dan Rugi Laba
     */
    private function prosesHPPRL($bulan, $tahun, $isHPP)
    {
        // Implementation for HPP and Profit/Loss processing
        Log::info("Proses HPP/RL started", ['bulan' => $bulan, 'tahun' => $tahun, 'isHPP' => $isHPP]);
        
        // Add your HPP/RL processing logic here
        // This is a placeholder - implement based on your business logic
    }

    /**
     * Proses Semua (equivalent to Delphi's Semua process)
     */
    private function prosesSemua($bulan, $tahun)
    {
        // Process all types in sequence (same as Delphi)
        $this->prosesAktiva($bulan, $tahun);
        $this->hitungUlangNeraca($bulan, $tahun);
        /*$this->hitungUlangAktiva($bulan, $tahun);
        $this->prosesHPPRugiLaba($bulan, $tahun);
        $this->prosesDashboard($bulan, $tahun);
        $this->prosesAktivaFiskal($bulan, $tahun);
        $this->hitungUlangAktivaFiskal($bulan, $tahun);*/
    }

    /**
     * Proses Per Devisi
     */
    private function prosesPerDevisi($bulan, $tahun)
    {
        // Implementation for per-division processing
        Log::info("Proses Per Devisi started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        // Add your per-division processing logic here
        // This is a placeholder - implement based on your business logic
    }

    /**
     * Proses Dashboard
     */
    private function prosesDashboard($bulan, $tahun)
    {
        // Implementation for dashboard processing
        Log::info("Proses Dashboard started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        // Add your dashboard processing logic here
        // This is a placeholder - implement based on your business logic
    }

    /**
     * Proses Aktiva Fiskal
     */
    private function prosesAktivaFiskal($bulan, $tahun)
    {
        // Implementation for fiscal asset processing
        Log::info("Proses Aktiva Fiskal started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        // Add your fiscal asset processing logic here
        // This is a placeholder - implement based on your business logic
    }

    /**
     * Hitung Ulang Aktiva Fiskal
     */
    private function hitungUlangAktivaFiskal($bulan, $tahun)
    {
        // Implementation for fiscal asset recalculation
        Log::info("Hitung Ulang Aktiva Fiskal started", ['bulan' => $bulan, 'tahun' => $tahun]);
        
        // Add your fiscal asset recalculation logic here
        // This is a placeholder - implement based on your business logic
    }
}

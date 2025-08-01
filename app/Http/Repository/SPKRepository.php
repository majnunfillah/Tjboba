<?php

namespace App\Http\Repository;

use App\Http\Repository\BaseRepository;
use App\Http\Services\CustomDatatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SPKRepository extends BaseRepository
{
    // TODO: Add SPK repository methods here

    public function getAllSpkLimited($limit = 10)
    {
        try {
            \Log::info('getAllSpkLimited function is called with limit: ' . $limit);
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return collect([]);
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            // Simple query with TOP for testing
            return DB::select("select TOP $limit A.NoBukti, A.Tanggal, A.NoSO, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.IsOtorisasi1, A.OtoUser1, A.TglOto1,
            Cast(Case when A.IsOtorisasi1=1 then 0 else 1 end As Bit) NeedOtorisasi,
            1 as DetailCount
            from dbSPK A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where year(A.Tanggal) = $tahun and month(A.Tanggal) = $bulan
            order by A.NoBukti");
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getAllSpkLimited: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function getAllSpk()
    {
        try {
            \Log::info('getAllSpk function is called.');
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return collect([]);
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            // Optimized query with detail count to avoid N+1 queries
            return DB::select("select A.NoBukti, A.Tanggal, A.NoSO, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.IsOtorisasi1, A.OtoUser1, A.TglOto1,
            Cast(Case when A.IsOtorisasi1=1 then 0 else 1 end As Bit) NeedOtorisasi,
            COALESCE(D.DetailCount, 0) as DetailCount
            from dbSPK A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            left join (
                select NoBukti, COUNT(*) as DetailCount
                from dbSPKDet
                group by NoBukti
            ) D on A.NoBukti = D.NoBukti
            where year(A.Tanggal) = $tahun and month(A.Tanggal) = $bulan
            order by A.NoBukti");
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getAllSpk: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function getSpkDetailByNoBukti($noBukti)
    {
        try {
            \Log::info('getSpkDetailByNoBukti function is called with NoBukti: ' . $noBukti);

            // First, let's check what columns actually exist in the table
            $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbSPKDet' ORDER BY ORDINAL_POSITION");
            $columnNames = array_map(function($col) { return $col->COLUMN_NAME; }, $columns);
            \Log::info('Available columns in dbSPKDet: ' . implode(', ', $columnNames));

            // Check if Urut column exists
            $hasUrut = in_array('Urut', $columnNames);
            \Log::info('Urut column exists: ' . ($hasUrut ? 'YES' : 'NO'));

            // Build query based on available columns
            if ($hasUrut) {
                $query = "select A.Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
                CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
                A.NoSat, A.Isi, '' as Keterangan
                from dbSPKDet A
                left join dbBarang B on A.KodeBrg = B.KodeBrg
                where A.NoBukti = ?
                order by A.Urut";
            } else {
                // If Urut doesn't exist, use ROW_NUMBER() to create it
                $query = "select ROW_NUMBER() OVER (ORDER BY A.KodeBrg) as Urut, A.KodeBrg, B.NamaBrg, A.Qnt,
                CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
                A.NoSat, A.Isi, '' as Keterangan
                from dbSPKDet A
                left join dbBarang B on A.KodeBrg = B.KodeBrg
                where A.NoBukti = ?";
            }

            $result = DB::select($query, [$noBukti]);
            \Log::info('getSpkDetailByNoBukti result count: ' . count($result));
            if ($result && count($result) > 0) {
                \Log::info('getSpkDetailByNoBukti first row keys: ' . implode(', ', array_keys((array)$result[0])));
            }
            return $result;
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getSpkDetailByNoBukti: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function testUrutColumn()
    {
        try {
            \Log::info('testUrutColumn function is called');

            // Test 1: Check if table exists and has Urut column
            $tableExists = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'dbSPKDet'");
            \Log::info('Table dbSPKDet exists: ' . ($tableExists[0]->count > 0 ? 'YES' : 'NO'));

            if ($tableExists[0]->count > 0) {
                // Test 2: Check if Urut column exists
                $columnExists = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'dbSPKDet' AND COLUMN_NAME = 'Urut'");
                \Log::info('Column Urut exists: ' . ($columnExists[0]->count > 0 ? 'YES' : 'NO'));

                // Test 3: Get sample data
                $sampleData = DB::select("SELECT TOP 1 * FROM dbSPKDet");
                if ($sampleData) {
                    \Log::info('Sample data keys: ' . implode(', ', array_keys((array)$sampleData[0])));
                }
            }

            return [
                'table_exists' => $tableExists[0]->count > 0,
                'column_exists' => $tableExists[0]->count > 0 ? ($columnExists[0]->count > 0) : false,
                'sample_keys' => $tableExists[0]->count > 0 && $sampleData ? array_keys((array)$sampleData[0]) : []
            ];
        } catch (\Exception $ex) {
            \Log::error('Error in testUrutColumn: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function getSpkByNoBukti($noBukti)
    {
        try {
            \Log::info('getSpkByNoBukti function is called with NoBukti: ' . $noBukti);
            return DB::select("select A.NoBukti, A.Tanggal, A.NoSO, A.KodeBrg, B.NamaBrg, A.Qnt,
            CASE WHEN A.NoSat=1 THEN B.SAT1 WHEN A.NoSat=2 THEN B.SAT2 ELSE '' END as Satuan,
            A.IsOtorisasi1, A.OtoUser1, A.TglOto1,
            Cast(Case when A.IsOtorisasi1=1 then 0 else 1 end As Bit) NeedOtorisasi
            from dbSPK A
            left join dbBarang B on A.KodeBrg = B.KodeBrg
            where A.NoBukti = ?", [$noBukti]);
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getSpkByNoBukti: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function deleteSpk($noBukti)
    {
        try {
            \Log::info('deleteSpk function is called with NoBukti: ' . $noBukti);

            // Delete detail records first
            $deletedDetails = DB::delete("DELETE FROM dbSPKDet WHERE NoBukti = ?", [$noBukti]);
            \Log::info('Deleted ' . $deletedDetails . ' detail records');

            // Delete header record
            $deletedHeader = DB::delete("DELETE FROM dbSPK WHERE NoBukti = ?", [$noBukti]);
            \Log::info('Deleted ' . $deletedHeader . ' header record');

            return $deletedHeader > 0;
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in deleteSpk: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function deleteSpkDetail($noBukti, $urut)
    {
        try {
            \Log::info('deleteSpkDetail function is called with NoBukti: ' . $noBukti . ', Urut: ' . $urut);

            $deleted = DB::delete("DELETE FROM dbSPKDet WHERE NoBukti = ? AND Urut = ?", [$noBukti, $urut]);
            \Log::info('Deleted ' . $deleted . ' detail record');

            return $deleted > 0;
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in deleteSpkDetail: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function setOtorisasi($request)
    {
        try {
            \Log::info('setOtorisasi function is called', [
                'NoBukti' => $request->NoBukti,
                'otoLevel' => $request->otoLevel,
                'status' => $request->status
            ]);

            $noBukti = $request->NoBukti;
            $otoLevel = $request->otoLevel;
            $status = $request->status;
            $userId = auth()->user()->USERID;
            $currentDate = now();            // Determine which otorisasi level to update
            $updateFields = [];
            if ($otoLevel == 'IsOtorisasi1') {
                $updateFields['IsOtorisasi1'] = $status;
                if ($status == 1) {
                    $updateFields['OtoUser1'] = $userId;
                    $updateFields['TglOto1'] = $currentDate;                } else {
                    // Untuk status = 0 (batalkan otorisasi), gunakan empty string untuk user
                    $updateFields['OtoUser1'] = '';  // Empty string instead of null
                    // Jika TglOto1 juga tidak boleh null, gunakan default date
                    $updateFields['TglOto1'] = '1900-01-01 00:00:00';  // Default date instead of null
                }}

            \Log::info('SPK setOtorisasi update fields', [
                'updateFields' => $updateFields,
                'noBukti' => $noBukti
            ]);

            // Update the SPK record
            $updated = DB::table('dbSPK')
                ->where('NoBukti', $noBukti)
                ->update($updateFields);

            \Log::info('Updated ' . $updated . ' SPK record for otorisasi');            return $updated > 0;
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in setOtorisasi: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function storeSpk($request)
    {
        try {
            \Log::info('storeSpk function is called');

            // Implementation for storing SPK
            // This would contain logic to insert new SPK record

            return true;
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in storeSpk: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function hasSpkDetailLevel2($noBukti, $urut)
    {
        try {
            \Log::info('hasSpkDetailLevel2 function is called', [
                'NoBukti' => $noBukti,
                'Urut' => $urut
            ]);

            $count = DB::select("SELECT COUNT(*) as count FROM dbSPKDetLevel2 WHERE NoBukti = ? AND UrutParent = ?", [$noBukti, $urut]);

            return $count[0]->count > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in hasSpkDetailLevel2: ' . $ex->getMessage());
            return false; // Return false if table doesn't exist or error
        }
    }
    public function getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut)
    {
        try {
            \Log::info('getSpkDetailLevel2ByNoBuktiAndUrut function is called', [
                'NoBukti' => $noBukti,
                'Urut' => $urut
            ]);

            return DB::select("
                DECLARE @NoBukti varchar(50), @Urut int

                SELECT @NoBukti = ?, @Urut = ?

                SELECT A.NOSPK, A.KodePrs, A.Urut, A.KODEMSN, A.TANGGAL, A.JAMAWAL, A.JAMAKHIR, A.QNTSPK
                        ,  A.Keterangan , C.ket
                        , B.NoBatch, B.TglExpired, B.tanggal TanggalBukti, B.NoUrut
                        , B.KodeBrg BrgJ, B.Qnt QntJ, B.Nosat NosatJ, B.Isi IsiJ, B.Satuan SatJ, B.KodeBOM,
                  A.TarifMesin, A.JamTenaker, A.JmlTenaker, A.TarifTenaker
                FROM DBJADWALPRD A
                LEFT OUTER JOIN DBSPK B ON B.NOBUKTI = A.NOSPK
                LEFT OUTER JOIN dbmesin C ON C.kodemsn = A.kodemsn
                WHERE A.NOSPK = @NoBukti
                  AND EXISTS (
                      SELECT 1 FROM dbSPKDet D
                      WHERE D.NoBukti = @NoBukti
                      AND (D.Urut = @Urut OR ROW_NUMBER() OVER (ORDER BY D.KodeBrg) = @Urut)
                  )
                ORDER BY A.Urut
            ", [$noBukti, $urut]);

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getSpkDetailLevel2ByNoBuktiAndUrut: ' . $ex->getMessage());
            return []; // Return empty array if table doesn't exist
        }
    }

    public function getSpkDetailLevel2AllByNoBukti($noBukti)
    {
        try {
            \Log::info('getSpkDetailLevel2AllByNoBukti function is called', [
                'NoBukti' => $noBukti
            ]);

            return DB::select("
                DECLARE @NoBukti varchar(50)

                SELECT @NoBukti = ?

                SELECT A.NOSPK, A.KodePrs, A.Urut, A.KODEMSN, A.TANGGAL, A.JAMAWAL, A.JAMAKHIR, A.QNTSPK
                        ,  A.Keterangan , C.ket
                        , B.NoBatch, B.TglExpired, B.tanggal TanggalBukti, B.NoUrut
                        , B.KodeBrg BrgJ, B.Qnt QntJ, B.Nosat NosatJ, B.Isi IsiJ, B.Satuan SatJ, B.KodeBOM,
                  A.TarifMesin, A.JamTenaker, A.JmlTenaker, A.TarifTenaker
                FROM DBJADWALPRD A
                LEFT OUTER JOIN DBSPK B ON B.NOBUKTI = A.NOSPK
                LEFT OUTER JOIN dbmesin C ON C.kodemsn = A.kodemsn
                WHERE A.NOSPK = @NoBukti
                ORDER BY A.Urut
            ", [$noBukti]);

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getSpkDetailLevel2AllByNoBukti: ' . $ex->getMessage());
            return []; // Return empty array if table doesn't exist
        }
    }

    public function deleteSpkDetailLevel2($noBukti, $urutParent, $urutChild)
    {
        try {
            \Log::info('deleteSpkDetailLevel2 function is called', [
                'NoBukti' => $noBukti,
                'UrutParent' => $urutParent,
                'UrutChild' => $urutChild
            ]);

            $deleted = DB::delete("DELETE FROM dbSPKDetLevel2 WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ?",
                [$noBukti, $urutParent, $urutChild]);
            \Log::info('Deleted ' . $deleted . ' level 2 detail record');

            return $deleted > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in deleteSpkDetailLevel2: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function storeSpkDetailLevel2($request)
    {
        try {
            \Log::info('storeSpkDetailLevel2 function is called');

            // Implementation untuk menyimpan data level 2
            // Contoh struktur data yang diharapkan:
            DB::insert("
                INSERT INTO dbSPKDetLevel2
                (NoBukti, UrutParent, UrutChild, KodeMaterial, Qnt, NoSat, Harga, Total, Keterangan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $request->NoBukti,
                $request->UrutParent,
                $request->UrutChild,
                $request->KodeMaterial,
                $request->Qnt,
                $request->NoSat,
                $request->Harga,
                $request->Total,
                $request->Keterangan
            ]);

            return true;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in storeSpkDetailLevel2: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function updateSpkDetailLevel2($request)
    {
        try {
            \Log::info('updateSpkDetailLevel2 function is called');

            $updated = DB::update("
                UPDATE dbSPKDetLevel2
                SET KodeMaterial = ?, Qnt = ?, NoSat = ?, Harga = ?, Total = ?, Keterangan = ?
                WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ?
            ", [
                $request->KodeMaterial,
                $request->Qnt,
                $request->NoSat,
                $request->Harga,
                $request->Total,
                $request->Keterangan,
                $request->NoBukti,
                $request->UrutParent,
                $request->UrutChild
            ]);

            return $updated > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in updateSpkDetailLevel2: ' . $ex->getMessage());
            throw $ex;
        }
    }

    // Methods for SPK Detail Level 3
    public function hasSpkDetailLevel3($noBukti, $urutParent, $urutChild)
    {
        try {
            \Log::info('hasSpkDetailLevel3 function is called', [
                'NoBukti' => $noBukti,
                'UrutParent' => $urutParent,
                'UrutChild' => $urutChild
            ]);

            $count = DB::selectOne("SELECT COUNT(*) as count FROM dbSPKDetLevel3 WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ?",
                [$noBukti, $urutParent, $urutChild]);

            return ($count->count ?? 0) > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in hasSpkDetailLevel3: ' . $ex->getMessage());
            return false;
        }
    }

    public function getSpkDetailLevel3ByNoBuktiAndUrut($noBukti, $urutParent, $urutChild)
    {
        try {
            \Log::info('getSpkDetailLevel3ByNoBuktiAndUrut function is called', [
                'NoBukti' => $noBukti,
                'UrutParent' => $urutParent,
                'UrutChild' => $urutChild
            ]);

            $data = DB::select("
                SELECT
                    NoBukti,
                    UrutParent,
                    UrutChild,
                    UrutGrandChild,
                    KdBrg,
                    NmBrg,
                    Satuan,
                    Qnt,
                    Harga,
                    Total,
                    Keterangan
                FROM dbSPKDetLevel3
                WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ?
                ORDER BY UrutGrandChild
            ", [$noBukti, $urutParent, $urutChild]);

            \Log::info('getSpkDetailLevel3ByNoBuktiAndUrut result count: ' . count($data));

            return collect($data);

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in getSpkDetailLevel3ByNoBuktiAndUrut: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function deleteSpkDetailLevel3($noBukti, $urutParent, $urutChild, $urutGrandChild)
    {
        try {
            \Log::info('deleteSpkDetailLevel3 function is called', [
                'NoBukti' => $noBukti,
                'UrutParent' => $urutParent,
                'UrutChild' => $urutChild,
                'UrutGrandChild' => $urutGrandChild
            ]);

            $deleted = DB::delete("DELETE FROM dbSPKDetLevel3 WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ? AND UrutGrandChild = ?",
                [$noBukti, $urutParent, $urutChild, $urutGrandChild]);
            \Log::info('Deleted ' . $deleted . ' level 3 detail record');

            return $deleted > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in deleteSpkDetailLevel3: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function storeSpkDetailLevel3($request)
    {
        try {
            \Log::info('storeSpkDetailLevel3 function is called', [
                'NoBukti' => $request->NoBukti,
                'UrutParent' => $request->UrutParent,
                'UrutChild' => $request->UrutChild
            ]);

            // Get next UrutGrandChild
            $maxUrut = DB::selectOne("SELECT ISNULL(MAX(UrutGrandChild), 0) as max_urut FROM dbSPKDetLevel3 WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ?",
                [$request->NoBukti, $request->UrutParent, $request->UrutChild]);

            $nextUrut = ($maxUrut->max_urut ?? 0) + 1;

            $inserted = DB::insert("
                INSERT INTO dbSPKDetLevel3 (
                    NoBukti, UrutParent, UrutChild, UrutGrandChild, KdBrg, NmBrg, Satuan, Qnt, Harga, Total, Keterangan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $request->NoBukti,
                $request->UrutParent,
                $request->UrutChild,
                $nextUrut,
                $request->KdBrg,
                $request->NmBrg,
                $request->Satuan,
                $request->Qnt,
                $request->Harga,
                $request->Total,
                $request->Keterangan
            ]);

            return $inserted;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in storeSpkDetailLevel3: ' . $ex->getMessage());
            throw $ex;
        }
    }

    public function updateSpkDetailLevel3($request)
    {
        try {
            \Log::info('updateSpkDetailLevel3 function is called', [
                'NoBukti' => $request->NoBukti,
                'UrutParent' => $request->UrutParent,
                'UrutChild' => $request->UrutChild,
                'UrutGrandChild' => $request->UrutGrandChild
            ]);

            $updated = DB::update("
                UPDATE dbSPKDetLevel3 SET
                    KdBrg = ?,
                    NmBrg = ?,
                    Satuan = ?,
                    Qnt = ?,
                    Harga = ?,
                    Total = ?,
                    Keterangan = ?
                WHERE NoBukti = ? AND UrutParent = ? AND UrutChild = ? AND UrutGrandChild = ?
            ", [
                $request->KdBrg,
                $request->NmBrg,
                $request->Satuan,
                $request->Qnt,
                $request->Harga,
                $request->Total,
                $request->Keterangan,
                $request->NoBukti,
                $request->UrutParent,
                $request->UrutChild,
                $request->UrutGrandChild
            ]);

            return $updated > 0;

        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error('Error in updateSpkDetailLevel3: ' . $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Get total count of SPK
     */
    public function getTotalSpk()
    {
        try {
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return 0;
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            $result = DB::select("
                SELECT COUNT(*) as total
                FROM dbSPK A
                WHERE year(A.Tanggal) = ? AND month(A.Tanggal) = ?
            ", [$tahun, $bulan]);

            return $result[0]->total ?? 0;

        } catch (\Exception $ex) {
            \Log::error('Error in getTotalSpk: ' . $ex->getMessage());
            return 0;
        }
    }

    /**
     * Get count of authorized SPK
     */
    public function getAuthorizedSpk()
    {
        try {
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return 0;
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            $result = DB::select("
                SELECT COUNT(*) as authorized
                FROM dbSPK A
                WHERE year(A.Tanggal) = ? AND month(A.Tanggal) = ? AND A.IsOtorisasi1 = 1
            ", [$tahun, $bulan]);

            return $result[0]->authorized ?? 0;

        } catch (\Exception $ex) {
            \Log::error('Error in getAuthorizedSpk: ' . $ex->getMessage());
            return 0;
        }
    }

    /**
     * Get count of pending SPK (not authorized)
     */
    public function getPendingSpk()
    {
        try {
            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return 0;
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            $result = DB::select("
                SELECT COUNT(*) as pending
                FROM dbSPK A
                WHERE year(A.Tanggal) = ? AND month(A.Tanggal) = ? AND A.IsOtorisasi1 = 0
            ", [$tahun, $bulan]);

            return $result[0]->pending ?? 0;

        } catch (\Exception $ex) {
            \Log::error('Error in getPendingSpk: ' . $ex->getMessage());
            return 0;
        }
    }

    /**
     * Get Outstanding SO data (SO yang belum selesai diproduksi)
     * Implementasi query SQL yang diberikan dengan konversi ke Laravel Query Builder
     */
    public function getOutstandingSo()
    {
        try {
            \Log::info('getOutstandingSo function is called');

            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return collect([]);
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            // Query SQL dengan optimasi performa - batasi hasil untuk menghindari timeout
            $query = "
                select A.NOBUKTI, A.URUT, A.KODEBRG, SUM(QntSO) QntSO, SUM(QntSPK) QntSPK, SUM(Saldo) Saldo,
                        B.NAMABRG, A.NOSAT,
                        case when A.NOSAT = 1 then SAT1
                            when A.NOSAT = 2 then SAT2
                            when A.NOSAT = 3 then SAT3 end Satuan ,max ( tglmulai ) tglmulai ,max(tglkirim) tglkirim
                                                ,tglkirim  tglselesai
                        from
                        (
                            select NOBUKTI, URUT, KODEBRG, QNT QntSO, 0 QntSPK, QNT+qnt2 Saldo, NOSAT,null tglmulai,null tglselesai
                            from DBSODET
                            union all
                            select A.NoSO, A.UrutSO, A.KODEBRG, 0 QntSO, A.Qnt QntSPK, -A.Qnt Saldo, A.Nosat,TglExpired tglmulai,tglselesai
                            from DBSPK A

                        )A
                        left outer join DBBARANG B on B.KODEBRG = A.KODEBRG
                        left outer join dbso c on c.nobukti=a.nobukti
                        where isnull(c.isbatal,0)=0
                        group by A.NOBUKTI, A.URUT, A.KODEBRG, B.NAMABRG, A.NOSAT, SAT1, SAT2, SAT3  ,tglkirim
                        having SUM(Saldo) <> 0
            ";

            $result = DB::select($query, [$tahun, $bulan]);

            \Log::info('getOutstandingSo result count: ' . count($result));

            return collect($result);

        } catch (\Exception $ex) {
            \Log::error('Error in getOutstandingSo: ' . $ex->getMessage());
            return collect([]);
        }
    }

    /**
     * Get Outstanding SO Summary statistics
     */
    /*public function getOutstandingSoSummary()
    {
        try {
            \Log::info('getOutstandingSoSummary function is called');

            $userid = auth()->user()->USERID;
            $periode = $this->queryModel('dbperiode')->where('USERID', $userid)->first();
            if (!$periode) {
                return [
                    'total_items' => 0,
                    'urgent_count' => 0,
                    'overdue_count' => 0,
                    'completion_rate' => 0
                ];
            }
            $tahun = $periode->TAHUN;
            $bulan = $periode->BULAN;

            // Query untuk mendapatkan summary Outstanding SO - dengan optimasi performa
            $summaryQuery = "
                WITH OutstandingData AS (
                    SELECT A.NOBUKTI, A.URUT, A.KODEBRG, SUM(QntSO) QntSO, SUM(QntSPK) QntSPK, SUM(Saldo) Saldo,
                        MAX(tglkirim) tglkirim
                    FROM (
                        SELECT TOP 5000 NOBUKTI, URUT, KODEBRG, QNT QntSO, 0 QntSPK, (QNT + ISNULL(qnt2, 0)) Saldo, NOSAT,
                            NULL tglmulai, NULL tglselesai
                        FROM DBSODET
                        ORDER BY NOBUKTI DESC

                        UNION ALL

                        SELECT A.NoSO, A.UrutSO, A.KODEBRG, 0 QntSO, A.Qnt QntSPK, -A.Qnt Saldo, A.Nosat,
                            A.TglExpired tglmulai, A.tglselesai
                        FROM DBSPK A
                        WHERE YEAR(A.Tanggal) = ? AND MONTH(A.Tanggal) = ?
                    ) A
                    LEFT OUTER JOIN DBSO C ON C.NOBUKTI = A.NOBUKTI
                    WHERE ISNULL(C.isbatal, 0) = 0
                    GROUP BY A.NOBUKTI, A.URUT, A.KODEBRG, C.tglkirim
                    HAVING SUM(Saldo) <> 0
                )
                SELECT
                    COUNT(*) as total_items,
                    SUM(CASE WHEN tglkirim IS NOT NULL AND DATEDIFF(day, GETDATE(), tglkirim) BETWEEN 0 AND 7 THEN 1 ELSE 0 END) as urgent_count,
                    SUM(CASE WHEN tglkirim IS NOT NULL AND tglkirim < GETDATE() THEN 1 ELSE 0 END) as overdue_count,
                    CASE WHEN SUM(QntSO) > 0 THEN CAST((SUM(QntSPK) * 100.0 / SUM(QntSO)) AS DECIMAL(5,2)) ELSE 0 END as completion_rate
                FROM OutstandingData
            ";

            $summaryResult = DB::select($summaryQuery, [$tahun, $bulan]);

            if ($summaryResult && count($summaryResult) > 0) {
                $summary = $summaryResult[0];
                return [
                    'total_items' => $summary->total_items ?? 0,
                    'urgent_count' => $summary->urgent_count ?? 0,
                    'overdue_count' => $summary->overdue_count ?? 0,
                    'completion_rate' => $summary->completion_rate ?? 0
                ];
            }

            return [
                'total_items' => 0,
                'urgent_count' => 0,
                'overdue_count' => 0,
                'completion_rate' => 0
            ];

        } catch (\Exception $ex) {
            \Log::error('Error in getOutstandingSoSummary: ' . $ex->getMessage());
            return [
                'total_items' => 0,
                'urgent_count' => 0,
                'overdue_count' => 0,
                'completion_rate' => 0
            ];
        }
    }*/
}
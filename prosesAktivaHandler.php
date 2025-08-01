public function prosesAktiva($bulan, $tahun)
{
    try {
        // Calculate the end of the month
        $akhirBulan = $this->getAkhirBulan($bulan, $tahun);
        
        // Disable specific database trigger
        DB::statement("ALTER TABLE dbo.dbTransaksi DISABLE TRIGGER TRI_Del_DBTRANSAKSI");

        // Delete transactions related to 'AKM' for the specified month and year
        DB::statement("
            DELETE FROM dbtransaksi
            WHERE Nobukti LIKE '%AKM%'
            AND MONTH(tanggal) = ?
            AND YEAR(tanggal) = ?
        ", [$bulan, $tahun]);

        DB::statement("
            DELETE FROM dbtrans
            WHERE Nobukti LIKE '%AKM%'
            AND MONTH(tanggal) = ?
            AND YEAR(tanggal) = ?
        ", [$bulan, $tahun]);

        // New Step: Fetch aktiva records using the SQL query
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
                a.Devisi,
                a.Tanggal
            FROM
                dbaktiva a
            WHERE
                a.tanggal <= @Ttgl
            ORDER BY
                a.Perkiraan;
        ", [$bulan, $tahun, $akhirBulan]);

        // Iterate through the results and execute the stored procedure for each `aktiva`
        foreach ($results as $aktiva) {
            DB::statement('EXEC Sp_prosesAktiva ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', [
                $bulan,
                $tahun,
                $aktiva->Devisi,           // From the retrieved dbaktiva table
                auth()->user()->USERID,    // Assuming IDUser is the logged-in user
                $akhirBulan,               // End of month
                $aktiva->perkiraan,        // From the dbaktiva table
                $aktiva->kodebag,          // From the dbaktiva table
                $aktiva->keterangan,       // From the dbaktiva table
                $aktiva->persen,           // From the dbaktiva table
                $aktiva->tipe,             // From the dbaktiva table
                $aktiva->akumulasi,        // From the dbaktiva table
                $aktiva->biaya,            // From the dbaktiva table
                $aktiva->persenbiaya1,     // From the dbaktiva table
                $aktiva->biaya2,           // From the dbaktiva table
                $aktiva->persenbiaya2,     // From the dbaktiva table
                $aktiva->biaya3,           // From the dbaktiva table
                $aktiva->persenbiaya3,     // From the dbaktiva table
                $aktiva->biaya4,           // From the dbaktiva table
                $aktiva->persenbiaya4,     // From the dbaktiva table
                $aktiva->TipeAktiva,       // From the dbaktiva table
                uniqid(),                  // Generate unique Nomor (replace if necessary)
                'NOMOR_BUKTI',             // Replace with logic to generate NomorBukti
                $aktiva->Tanggal           // Date field from the aktiva record
            ]);
        }

        // Re-enable the trigger
        DB::statement("ALTER TABLE dbo.dbTransaksi ENABLE TRIGGER TRI_Del_DBTRANSAKSI");

        // Return success response
        return response()->json(['success' => true, 'message' => 'Proses Aktiva berhasil dilakukan.']);

    } catch (\Exception $e) {
        // Rollback transaction on error
        DB::rollBack();

        // Return error response
        return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    }
}
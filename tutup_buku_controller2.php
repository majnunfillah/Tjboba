<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutupBukuController extends Controller
{
    /**
     * Execute the Sp_prosesAktiva Stored Procedure.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function executeProsesAktiva(Request $request)
    {
        try {
            // Validate request input
            $validated = $request->validate([
                'bulan' => 'required|integer',
                'tahun' => 'required|integer',
                'idUser' => 'required|integer',
                'ttgl' => 'required|date',
                'nomor' => 'required|string',
                'nomorBukti' => 'required|string',
            ]);

            // Fetch data from QuCari table
            $data = DB::table('QuCari')->select(
                'Devisi',
                'Perkiraan',
                'KodeBag',
                'Keterangan',
                'Persen',
                'Tipe',
                'Akumulasi',
                'Biaya',
                'PersenBiaya1',
                'Biaya2',
                'PersenBiaya2',
                'Biaya3',
                'PersenBiaya3',
                'Biaya4',
                'PersenBiaya4',
                'TipeAktiva',
                'Tanggal'
            )->first();

            // Guard clause if no data is found
            if (is_null($data)) {
                return response()->json(['error' => 'Data not found in QuCari table.'], 404);
            }

            // Execute the stored procedure
            DB::statement('EXEC Sp_prosesAktiva ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', [
                $validated['bulan'],                // Parameter 1
                $validated['tahun'],                // Parameter 2
                $data->Devisi,                      // Parameter 3
                $validated['idUser'],               // Parameter 4
                $validated['ttgl'],                 // Parameter 5
                $data->Perkiraan,                   // Parameter 6
                $data->KodeBag,                     // Parameter 7
                $data->Keterangan,                  // Parameter 8
                $data->Persen,                      // Parameter 9
                $data->Tipe,                        // Parameter 10
                $data->Akumulasi,                   // Parameter 11
                $data->Biaya,                       // Parameter 12
                $data->PersenBiaya1,                // Parameter 13
                $data->Biaya2,                      // Parameter 14
                $data->PersenBiaya2,                // Parameter 15
                $data->Biaya3,                      // Parameter 16
                $data->PersenBiaya3,                // Parameter 17
                $data->Biaya4,                      // Parameter 18
                $data->PersenBiaya4,                // Parameter 19
                $data->TipeAktiva,                  // Parameter 20
                $validated['nomor'],                // Parameter 21
                $validated['nomorBukti'],           // Parameter 22
                $data->Tanggal                      // Parameter 23
            ]);

            // Return success response
            return response()->json(['message' => 'Proses Sp_prosesAktiva berhasil dilakukan.'], 200);

        } catch (\Throwable $e) {
            // Handle errors (exception handling)
            return response()->json(['error' => 'Proses Gagal. Error: ' . $e->getMessage()], 500);
        }
    }
}
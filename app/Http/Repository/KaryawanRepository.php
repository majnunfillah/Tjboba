<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;

class KaryawanRepository
{
    /**
     * Search karyawan by keyword
     */
    public function searchKaryawan($keyword)
    {
        return DB::table('dbKaryawan')
            ->select([
                'KodeKry',
                'NamaKry',
                'Jabatan'
            ])
            ->where(function($q) use ($keyword) {
                $q->where('KodeKry', 'like', '%' . $keyword . '%')
                  ->orWhere('NamaKry', 'like', '%' . $keyword . '%');
            })
            ->orderBy('NamaKry')
            ->limit(50)
            ->get();
    }

    /**
     * Get karyawan by kode
     */
    public function getKaryawanByKode($kode)
    {
        return DB::table('dbKaryawan')
            ->where('KodeKry', $kode)
            ->first();
    }

    /**
     * Get all karyawan
     */
    public function getAllKaryawan()
    {
        return DB::table('dbKaryawan')
            ->orderBy('NamaKry')
            ->get();
    }
} 
<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;

class MesinRepository
{
    /**
     * Search mesin by keyword
     */
    public function searchMesin($keyword)
    {
        return DB::table('dbMesin')
            ->select([
                'KodeMsn',
                'Ket',
                'Keterangan'
            ])
            ->where(function($q) use ($keyword) {
                $q->where('KodeMsn', 'like', '%' . $keyword . '%')
                  ->orWhere('Ket', 'like', '%' . $keyword . '%');
            })
            ->orderBy('KodeMsn')
            ->limit(50)
            ->get();
    }

    /**
     * Get mesin by kode
     */
    public function getMesinByKode($kode)
    {
        return DB::table('dbMesin')
            ->where('KodeMsn', $kode)
            ->first();
    }

    /**
     * Get all mesin
     */
    public function getAllMesin()
    {
        return DB::table('dbMesin')
            ->orderBy('KodeMsn')
            ->get();
    }
} 
<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;

class SORepository
{
    /**
     * Search SO by keyword
     */
    public function searchSO($keyword)
    {
        return DB::table('dbSO')
            ->select([
                'NoBukti',
                'Tanggal',
                'Customer',
                'Status'
            ])
            ->where(function($q) use ($keyword) {
                $q->where('NoBukti', 'like', '%' . $keyword . '%')
                  ->orWhere('Customer', 'like', '%' . $keyword . '%');
            })
            ->where('IsBatal', 0)
            ->orderBy('NoBukti', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get default SO
     */
    public function getDefaultSO()
    {
        return DB::table('dbSO')
            ->where('IsBatal', 0)
            ->orderBy('Tanggal', 'desc')
            ->first();
    }

    /**
     * Get SO by number
     */
    public function getSOByNumber($noBukti)
    {
        return DB::table('dbSO')
            ->where('NoBukti', $noBukti)
            ->where('IsBatal', 0)
            ->first();
    }

    /**
     * Get SO details
     */
    public function getSODetails($noBukti)
    {
        return DB::table('dbSODet as A')
            ->leftJoin('dbBarang as B', 'A.KodeBrg', '=', 'B.KodeBrg')
            ->select([
                'A.*',
                'B.NamaBrg'
            ])
            ->where('A.NoBukti', $noBukti)
            ->orderBy('A.Urut')
            ->get();
    }
} 
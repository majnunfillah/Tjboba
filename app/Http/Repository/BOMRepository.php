<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;

class BOMRepository
{
    /**
     * Get BOM by kode barang and kode BOM
     */
    public function getBOM($kodeBrg, $kodeBOM)
    {
        return DB::table('dbBOM')
            ->where('KodeBrg', $kodeBrg)
            ->where('KodeBOM', $kodeBOM)
            ->first();
    }

    /**
     * Get BOM details
     */
    public function getBOMDetails($kodeBOM)
    {
        return DB::table('dbBOMDet as A')
            ->leftJoin('dbBarang as B', 'A.KodeBrg', '=', 'B.KodeBrg')
            ->select([
                'A.*',
                'B.NamaBrg'
            ])
            ->where('A.KodeBOM', $kodeBOM)
            ->orderBy('A.Urut')
            ->get();
    }

    /**
     * Search BOM by keyword
     */
    public function searchBOM($keyword)
    {
        return DB::table('dbBOM as A')
            ->leftJoin('dbBarang as B', 'A.KodeBrg', '=', 'B.KodeBrg')
            ->select([
                'A.KodeBOM',
                'A.KodeBrg',
                'B.NamaBrg',
                'A.Keterangan'
            ])
            ->where(function($q) use ($keyword) {
                $q->where('A.KodeBOM', 'like', '%' . $keyword . '%')
                  ->orWhere('A.KodeBrg', 'like', '%' . $keyword . '%')
                  ->orWhere('B.NamaBrg', 'like', '%' . $keyword . '%');
            })
            ->orderBy('A.KodeBOM')
            ->limit(50)
            ->get();
    }

    /**
     * Get BOM by kode barang
     */
    public function getBOMByBarang($kodeBrg)
    {
        return DB::table('dbBOM')
            ->where('KodeBrg', $kodeBrg)
            ->orderBy('KodeBOM')
            ->get();
    }
} 
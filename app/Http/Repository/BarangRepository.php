<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;

class BarangRepository
{
    /**
     * Search barang by keyword
     */
    public function searchBarang($keyword, $jenis = null)
    {
        $query = DB::table('dbBarang')
            ->select([
                'KodeBrg',
                'NamaBrg',
                'SAT1',
                'SAT2',
                'SAT3',
                'KodeGrp'
            ])
            ->where(function($q) use ($keyword) {
                $q->where('KodeBrg', 'like', '%' . $keyword . '%')
                  ->orWhere('NamaBrg', 'like', '%' . $keyword . '%');
            });
        
        if ($jenis) {
            $query->where('KodeGrp', $jenis);
        }
        
        return $query->orderBy('KodeBrg')->limit(50)->get();
    }

    /**
     * Get barang by kode
     */
    public function getBarangByKode($kode)
    {
        return DB::table('dbBarang')
            ->where('KodeBrg', $kode)
            ->first();
    }

    /**
     * Get barang jadi (finished goods)
     */
    public function getBarangJadi()
    {
        return DB::table('dbBarang')
            ->where('KodeGrp', 'BJ')
            ->orderBy('KodeBrg')
            ->get();
    }

    /**
     * Get bahan (materials)
     */
    public function getBahan()
    {
        return DB::table('dbBarang')
            ->where('KodeGrp', 'Bahan')
            ->orderBy('KodeBrg')
            ->get();
    }
} 
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\BrowseRequest;

class BrowseController extends Controller
{
    public function index()
    {
        return view('browse.index');
    }

    public function search(Request $request)
    {
        $kodeBrows = $request->input('kode_brows');
        $filter = $request->input('filter', '');
        $isData = $request->input('is_data', '');
        $noKira = $request->input('no_kira', '');
        
        $data = [];
        
        switch ($kodeBrows) {
            case '100101': // Gudang
                $data = $this->getGudangData($filter, $isData);
                break;
                
            case '120302': // Barang
                $data = $this->getBarangData($filter);
                break;
                
            case '81': // Customer Member
                $data = $this->getCustomerMemberData($filter);
                break;
                
            case '11001': // Valas
                $data = $this->getValasData();
                break;
                
            default:
                $data = [];
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getGudangData($filter, $excludeGudang)
    {
        $query = DB::table('db_gudang as a')
            ->leftJoin('db_pemakai_gdg as b', 'b.kodegdg', '=', 'a.kodegdg')
            ->select('a.KodeGdg', 'a.Nama as NamaGdg', 'a.IsRusak')
            ->where('a.Kodegdg', '!=', $excludeGudang)
            ->orderBy('a.KodeGdg');
            
        if (!empty($filter)) {
            $query->where(function($q) use ($filter) {
                $q->where('a.KodeGdg', 'like', "%{$filter}%")
                  ->orWhere('a.Nama', 'like', "%{$filter}%");
            });
        }
        
        return $query->get();
    }

    private function getBarangData($filter)
    {
        $query = DB::table('vw_barang as a')
            ->select('a.KodeBrg', 'a.NamaBrg', 'a.Sat1', 'a.Sat2', 'a.Isi1', 'a.Isi2', 'a.NFix')
            ->where('a.IsBarang', 1)
            ->where('a.IsAktif', 1)
            ->orderBy('a.KodeBrg');
            
        if (!empty($filter)) {
            $query->where(function($q) use ($filter) {
                $q->where('a.KodeBrg', 'like', "%{$filter}%")
                  ->orWhere('a.NamaBrg', 'like', "%{$filter}%");
            });
        }
        
        return $query->get();
    }

    private function getCustomerMemberData($filter)
    {
        $query = DB::table('db_custsupp')
            ->select('KodeCustSupp', 'NamaCustSupp', 
                    DB::raw("CONCAT(Alamat1, CHAR(13), Alamat2, CHAR(13), kota) as Alamat"),
                    'Telpon', 'DiscMember')
            ->where('IsMember', 1)
            ->where('IsAktif', 1)
            ->orderBy('KodeCustSupp');
            
        if (!empty($filter)) {
            $query->where(function($q) use ($filter) {
                $q->where('KodeCustSupp', 'like', "%{$filter}%")
                  ->orWhere('NamaCustSupp', 'like', "%{$filter}%");
            });
        }
        
        return $query->get();
    }

    private function getValasData()
    {
        return DB::table('db_valas')
            ->select('KodeVls', 'NamaVls', 'Kurs')
            ->orderBy('KodeVls')
            ->get();
    }

    public function getData(Request $request)
    {
        $selectedId = $request->input('id');
        $kodeBrows = $request->input('kode_brows');
        
        // Return selected data based on kode_brows
        $data = null;
        
        switch ($kodeBrows) {
            case '100101':
                $data = DB::table('db_gudang')->where('KodeGdg', $selectedId)->first();
                break;
            case '120302':
                $data = DB::table('vw_barang')->where('KodeBrg', $selectedId)->first();
                break;
            case '81':
                $data = DB::table('db_custsupp')->where('KodeCustSupp', $selectedId)->first();
                break;
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
} 
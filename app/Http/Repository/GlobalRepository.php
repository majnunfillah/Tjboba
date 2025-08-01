<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\GlobalInterface;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalRepository extends BaseRepository implements GlobalInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getKelompokKasOrBank($type = 'KAS', $query = null)
    {
        return $this->queryModel('dbperkiraan')
            ->select('Perkiraan', 'Keterangan')
            ->whereHas('kelompok_kas', function ($q) use ($type) {
                $q->where('Kode', $type);
            })
            ->when($query !== NULL, function ($q) use ($query) {
                return $q->where('Keterangan', 'like', '%' . $query . '%');
            })
            ->get();
    }

    public function storeKelompokKasOrBank($perkiraan, $oldPerkiraan = null, $type = 'KAS')
    {
        try {
            DB::beginTransaction();
            if ($oldPerkiraan !== null) {
                $this->queryModel('dbposthutpiut')->where('Perkiraan', $oldPerkiraan)->where('Kode', $type)->delete();
            }
            if ($perkiraan == null) {
                return false;
            }

            $this->queryModel('dbposthutpiut')->create(['Perkiraan' => $perkiraan, 'Kode' => $type]);
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }

    public function deleteKelompokKasOrBank($perkiraan, $type = 'KAS')
    {
        try {
            DB::beginTransaction();
            $this->queryModel('dbposthutpiut')->where('Kode', $type)->where('Perkiraan', $perkiraan)->delete();
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }

    public function getKelompokAktiva()
    {
        return $this->queryModel('dbperkiraan')->from('dbperkiraan as p')
            ->select(
                'p.Perkiraan',
                'p.Keterangan',
                DB::raw("Case when php.Tipe='L' then '[L]urus' when php.Tipe='M' then '[M]enurun' when php.Tipe='P' then '[P]ajak' else '' end Metode"),
                'php.Persen',
                'Akumulasi',
                'p1.Keterangan as KeteranganAkumulasi',
                'Biaya1',
                'p2.Keterangan as KeteranganBiaya1',
                'PersenBiaya1',
                'Biaya2',
                'PersenBiaya2'
            )
            ->join('dbposthutpiut as php', function ($q) {
                $q->on('php.Perkiraan', '=', 'p.Perkiraan');
                $q->where('php.Kode', '=', 'AKV');
            })
            ->join('dbperkiraan as p1', 'p1.Perkiraan', '=', 'php.Akumulasi')
            ->leftjoin('dbperkiraan as p2', 'p2.Perkiraan', '=', 'php.Biaya1')
            ->get();
    }

    public function storeKelompokAktiva(Request $request, $oldPerkiraan = null)
    {
        try {
            $request->validate([
                'Perkiraan' => ['required_without:oldPerkiraan', 'string', 'max:20'],
                'Akumulasi' => ['required', 'string', 'max:20'],
                'Biaya1' => ['required', 'string', 'max:20'],
                'PersenBiaya1' => ['required', 'numeric', 'max:100'],
                'Biaya2' => ['nullable', 'string', 'max:20'],
                'PersenBiaya2' => ['nullable', 'numeric', 'max:100'],
                'Tipe' => ['required', 'string', 'max:1'],
                'Persen' => ['required', 'numeric', 'max:100'],
            ]);

            DB::beginTransaction();


            if ($oldPerkiraan !== null) {
                $this->queryModel('dbposthutpiut')
                    ->where('Perkiraan', $oldPerkiraan)
                    ->where('Kode', 'AKV')->update([
                        'Akumulasi' => $request->Akumulasi,
                        'Biaya1' => $request->Biaya1,
                        'PersenBiaya1' => $request->PersenBiaya1,
                        'Biaya2' => $request->Biaya2 ?? '',
                        'PersenBiaya2' => $request->PersenBiaya2 ?? 0,
                        'Tipe' => $request->Tipe,
                        'Persen' => $request->Persen,
                    ]);
            } else {
                $this->queryModel('dbposthutpiut')
                    ->create([
                        'Akumulasi' => $request->Akumulasi,
                        'Biaya1' => $request->Biaya1,
                        'PersenBiaya1' => $request->PersenBiaya1,
                        'Biaya2' => $request->Biaya2 ?? '',
                        'PersenBiaya2' => $request->PersenBiaya2 ?? 0,
                        'Tipe' => $request->Tipe,
                        'Persen' => $request->Persen,
                        'Perkiraan' => $request->Perkiraan,
                        'Kode' => 'AKV',
                    ]);
            }

            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }

    public function deleteKelompokAktiva($perkiraan)
    {
        try {
            DB::beginTransaction();
            $this->queryModel('dbposthutpiut')->where('Kode', 'AKV')->where('Perkiraan', $perkiraan)->delete();
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }

    public function getNomorSPK()
    {
        try {
            $data = $this->queryModel('dbbarang')
                ->select('NOBUKTI', 'dbbarang.KODEBRG', 'NAMABRG', 'NoSO', '')
                ->join('dbspk', 'dbspk.KODEBRG', '=', 'KODEBRG')->get();
            return $data;
        } catch (QueryException $e) {
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }

    public function getCustomerHutang($JENIS = NULL)
    {
        try {
            return $this->queryModel('dbcustsupp')->when($JENIS != NULL, function($q) use ($JENIS) {
                if($JENIS == 'HT'){
                    $q->where('JENIS', 0);
                }else if($JENIS == 'PT'){
                    $q->where('JENIS', 1);
                }
            })->orderBy('KODECUSTSUPP')->get();
        } catch (QueryException $e) {
            return abort(501, "Terjadi Kesalahan saat menjalankan Query. Error Message : " . $e->getMessage() . " Error Line : " . $e->getLine());
        }
    }
}

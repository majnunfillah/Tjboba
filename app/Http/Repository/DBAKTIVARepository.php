<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\DBAKTIVAInterface;
use App\Http\Requests\AktivaRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class DBAKTIVARepository extends BaseRepository implements DBAKTIVAInterface
{

  public function __construct()
  {
    parent::__construct(['DBAKTIVA', 'DBPERKIRAAN']);
  }

  public function getAktiva($perkiraan, $devisi)
  {
    return $this->queryModel('dbaktiva')
      ->with('NoMuka:Perkiraan,Keterangan', 'Akumulasi:Perkiraan,Keterangan', 'devisi:Devisi,NamaDevisi', 'biaya:Perkiraan,Keterangan')
      ->where('Perkiraan', $perkiraan)
      ->where('Devisi', $devisi)
      ->firstOrNew();
  }

  public function getAllAktiva($devisi, $tipeAktiva)
  {
    return $this->queryModel('dbaktiva')
      ->select(
        'dbaktiva.Perkiraan as KodeAktiva',
        'dbaktiva.Tanggal',
        'dbaktiva.Devisi',
        'dbdevisi.NamaDevisi',
        DB::raw("Case when dbaktiva.TipeAktiva=0 then 'Aktiva Tetap' when dbaktiva.TipeAktiva=1 then 'Aktiva Yang Dibiayakan' when dbaktiva.Tipeaktiva=2 then 'Aktiva Dalam Penyelesaian' else '' end MyTipe"),
        'dbaktiva.TipeAktiva',
        'dbaktiva.NoMuka as GroupAktiva',
        'dbperkiraan.Keterangan as NamaPerkiraan',
        'dbaktiva.Keterangan',
        'dbaktiva.kodeBag',
        'dbbagian.Namabag',
        'dbaktiva.Quantity',
        'dbaktiva.Persen as Susut',
        'dbaktiva.Tipe',
        DB::raw("Case when dbaktiva.Tipe='L' then '[L]urus' when dbaktiva.Tipe='M' then '[M]enurun' when dbaktiva.Tipe='P' then '[P]ajak' else '' end Metode"),
        'dbaktiva.akumulasi',
        'dbaktiva.Biaya',
        'dbaktiva.PersenBiaya1',
        'dbaktiva.Biaya2',
        'dbaktiva.PersenBiaya2',
        'dbaktiva.Biaya3',
        'dbaktiva.PersenBiaya3',
        'dbaktiva.Biaya4',
        'dbaktiva.persenbiaya4',
        'dbperkiraan.Keterangan as KetAkm',
        'dbaktiva.nobelakang',
        'dbaktiva.NoBelakang2',
        'dbaktiva.Kelompok'
      )->leftJoin('dbperkiraan', 'dbperkiraan.perkiraan', '=', 'dbaktiva.NoMuka')
      ->leftJoin('dbdevisi', 'dbdevisi.devisi', '=', 'dbaktiva.devisi')
      ->leftJoin('dbbagian', 'dbbagian.kodebag', '=', 'dbaktiva.kodebag')
      ->leftJoin('dbperkiraan as dbperkiraan2', 'dbperkiraan2.perkiraan', '=', 'dbaktiva.akumulasi')
      ->when($devisi != null, function ($q) use ($devisi) {
        return $q->where('dbaktiva.devisi', $devisi)
          ->orWhereRaw("Case when '$devisi'=dbaktiva.Devisi then '$devisi' else 'Semua' end='Semua'");
      })
      ->when($tipeAktiva != null, function ($q) use ($tipeAktiva) {
        return $q->where('dbaktiva.tipeaktiva', $tipeAktiva)
          ->orWhereRaw("Case when '$tipeAktiva'=0 then 0 
          when '$tipeAktiva'=1 then 1 
          when '$tipeAktiva'=2 then 2 
          else 3 end=3");
      })
      ->orderBy('dbaktiva.NoMuka', 'ASC')
      ->orderBy('dbaktiva.Perkiraan', 'ASC')
      ->orderBy('dbaktiva.Devisi', 'ASC')
      ->get();
  }

  public function detailAktiva($kodeaktiva)
  {
    // exec Sp_LapSaldoAwal :0,:1,:2,:3
    return $this->queryModel('dbaktiva')
      ->select(
        'dbaktiva.Perkiraan as KodeAktiva',
        'dbaktiva.Keterangan',
        DB::raw("Cast(dbaktiva.Persen as varchar(12))+'%' Susut"),
        DB::raw("Case when dbaktiva.Tipe='L' then '[L]urus'
             when dbaktiva.Tipe='M' then '[M]enurun'
             when dbaktiva.Tipe='P' then '[P]ajak'
             else ''
        end Metode"),
        'b.Bulan',
        'b.Tahun',
        'b.Awal',
        'b.AwalD',
        'b.MD',
        'b.DMD',
        'b.MK',
        'b.DMK',
        'b.Akhir',
        'b.AkhirD',
        'b.AwalSusut',
        'b.AwalSusutD',
        'b.SD',
        'b.DSD',
        'b.SK',
        'b.DSK',
        'b.AkhirSusut',
        'b.AkhirSusutD',
        DB::raw("CAST(B.Tahun as varchar(4))+Case when b.Bulan<10 then '0' else '' end+CAST(B.Bulan as Varchar(2)) Periode"),
        DB::raw("B.Akhir-B.AkhirSusut NilaiAK")
      )
      ->leftJoin('dbaktivadet as b', function ($join) {
        $join->on('b.Perkiraan', '=', 'dbaktiva.Perkiraan')
          ->on('b.Devisi', '=', 'dbaktiva.Devisi');
      })
      ->where('dbaktiva.Perkiraan', $kodeaktiva)
      ->orderBy('dbaktiva.Perkiraan', 'ASC')
      ->orderBy(DB::raw("CAST(B.Tahun as varchar(4))+Case when b.Bulan<10 then '0' else '' end+CAST(B.Bulan as Varchar(2))"), 'ASC')
      ->get();
  }

  public function storeAktiva(AktivaRequest $request)
  {
    try {
      DB::beginTransaction();
      $this->queryModel('dbaktiva')->create($request->validated());
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function updateAktiva(Request $request, $perkiraan, $devisi)
  {
    try {
      DB::beginTransaction();
      $this->queryModel('dbaktiva')
        ->where('Perkiraan', $perkiraan)
        ->where('Devisi', $devisi)
        ->update($request->validated());
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function deleteAktiva($perkiraan, $devisi)
  {
    try {
      DB::beginTransaction();
      $this->queryModel('dbaktiva')
        ->where('Perkiraan', $perkiraan)
        ->where('Devisi', $devisi)->delete();
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function getSaldoAwal($perkiraan, $devisi, $periode)
  {
    if ($periode->Bulan < 10) {
      $periode->BULAN = substr($periode->BULAN, 1);
    }

    return $this->queryModel('dbaktivadet')
      ->select(
        'Valas',
        'Kurs',
        'Awal',
        'AwalSusut',
      )
      ->where('Perkiraan', $perkiraan)
      ->where('Devisi', $devisi)
      ->where('Bulan', $periode->BULAN)
      ->where('Tahun', $periode->TAHUN)
      ->firstOrNew();
  }

  public function setSaldoAwal(Request $request, $perkiraan, $devisi, $periode)
  {
    $validated = $request->validate([
      'Valas' => 'nullable|string',
      'Kurs' => 'nullable|numeric',
      'Awal' => 'nullable|numeric',
      'AwalSusut' => 'nullable|numeric',
    ]);
    $validated['Valas']       = $validated['Valas'] ?? '';
    $validated['Kurs']        = $validated['Kurs'] ?? 0;
    $validated['Awal']        = $validated['Awal'] ?? 0;
    $validated['AwalSusut']   = $validated['AwalSusut'] ?? 0;

    if ($periode->Bulan < 10) {
      $periode->BULAN = substr($periode->BULAN, 1);
    }
    try {
      DB::beginTransaction();
      $aktivadet = $this->queryModel('dbaktivadet')
        ->where('Perkiraan', $perkiraan)
        ->where('Devisi', $devisi)
        ->where('Bulan', $periode->BULAN)
        ->where('Tahun', $periode->TAHUN)
        ->firstOrNew();

      if (!$aktivadet->exists) {
        $aktivadet->Perkiraan = $perkiraan;
        $aktivadet->Devisi = $devisi;
        $aktivadet->Bulan = $periode->BULAN;
        $aktivadet->Tahun = $periode->TAHUN;
      }
      $aktivadet->Valas         = $validated['Valas'];
      $aktivadet->Kurs          = $validated['Kurs'];
      $aktivadet->Awal          = $validated['Awal'];
      $aktivadet->AwalSusut     = $validated['AwalSusut'];
      $aktivadet->AwalD         = $validated['Awal'];
      $aktivadet->AwalSusutD    = $validated['AwalSusut'];
      // $aktivadet->Akhir         = $validated['Awal'];
      // $aktivadet->AkhirSusut    = $validated['AwalSusut'];
      // $aktivadet->AkhirD        = $validated['Awal'];
      // $aktivadet->AkhirSusutD   = $validated['AwalSusut'];
      $aktivadet->save();

      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }
}

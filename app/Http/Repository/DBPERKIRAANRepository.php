<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\DBPERKIRAANInterface;
use App\Http\Requests\PerkiraanRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class DBPERKIRAANRepository extends BaseRepository implements DBPERKIRAANInterface
{

  public function __construct()
  {
    parent::__construct(['DBAKSESPERKIRAAN', 'DBFLPASS', 'DBPERKIRAAN']);
  }

  public function getPerkiraan($perkiraan)
  {
    return $this->queryModel('dbperkiraan')->with('valas', 'aruskas', 'aruskasdet')->where('Perkiraan', $perkiraan)->firstOrNew();
  }

  public function getAllPerkiraan()
  {
    return $this->queryModel('dbperkiraan')->with('aruskas', 'aruskasdet')->orderBy('Perkiraan', 'ASC')->get();
  }

  public function storePerkiraan(PerkiraanRequest $request)
  {
    try {
      DB::beginTransaction();
      $validated = $request->validated();
      $this->queryModel('dbperkiraan')->create($validated);
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function updatePerkiraan(PerkiraanRequest $request, $Perkiraan)
  {
    try {
      DB::beginTransaction();
      $validated = $request->validated();
      $this->queryModel('dbperkiraan')->where('Perkiraan', $Perkiraan)->update($validated);
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function deletePerkiraan($Perkiraan)
  {
    try {
      DB::beginTransaction();
      $this->queryModel('dbperkiraan')->where('Perkiraan', $Perkiraan)->delete();
      $this->queryModel('dbneraca')->where('Perkiraan', $Perkiraan)->delete();
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function getBudget($perkiraan, $tahun)
  {
    return DB::select(DB::raw("Select a.Perkiraan,b.Devisi,b.Bulan,b.Tahun,b.Md,b.Mk, b.Budget
from DBPERKIRAAN a
     left outer join dbNERACA b on b.perkiraan=a.perkiraan
where a.Perkiraan='$perkiraan' and Tahun='$tahun' 
Order by a.Perkiraan"));
  }

  
  public function setBudget(Request $request, $perkiraan)
  {
    try {
      DB::beginTransaction();
      $data = [
        'Budget' => floatval($request->Budget_val),
      ];
      $this->queryModel('dbneraca')->where('Perkiraan', $perkiraan)
      ->where('Devisi' , $request->Devisi)
      ->where('Bulan' , $request->Bulan)
      ->where('Tahun' , $request->Tahun)
      ->update($data);
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function getSaldoAwal($perkiraan, $bulan, $tahun)
  {
    if(! $this->queryModel('dbneraca')->where('Perkiraan', $perkiraan)->exists()){
      $this->createNeraca($perkiraan, $bulan, $tahun);
    }
    return DB::select(DB::raw("Select a.Perkiraan,a.Keterangan,c.NamaDevisi,a.DK,b.valas,b.kurs,
    b.AwalD,b.AwalDRp,b.AwalK,b.AwalKRp,b.Devisi,b.Bulan,b.Tahun
from DBPERKIRAAN a
     left outer join dbNERACA b on b.perkiraan=a.perkiraan
     left outer join dbdevisi c on c.devisi=b.Devisi
where a.Perkiraan='$perkiraan' and Bulan='$bulan' and Tahun='$tahun' 
Order by a.Perkiraan"));
  }

  public function setSaldoAwal(Request $request, $perkiraan)
  {
    try {
      DB::beginTransaction();
      $data = [
        'AwalD' => floatval($request->AwalD_val),
        'AwalDRp' => floatval($request->AwalDRp_val),
        'Kurs' => floatval($request->kurs_val),
        'Valas' => $request->valas == null ? '' : $request->valas,
      ];
      // dd($data);
      $this->queryModel('dbneraca')->where('Perkiraan', $perkiraan)
      ->where('Devisi' , $request->Devisi)
      ->where('Bulan' , $request->Bulan)
      ->where('Tahun' , $request->Tahun)
      ->update($data);
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function getAccessCOA($USERID)
  {
    return $this->queryModel('dbperkiraan')->select('DBPERKIRAAN.Perkiraan', 'Keterangan', 'ap.UserId')
      ->leftjoin('DBAKSESPERKIRAAN as ap', function ($q) use ($USERID) {
        $q->on('ap.Perkiraan', '=', 'dbperkiraan.Perkiraan')
          ->where('ap.UserID', '=', $USERID);
      })->get();
  }

  public function updateAccessCOA(Request $request, $USERID)
  {
    try {
      DB::beginTransaction();
      $user = $this->queryModel('dbflpass')->where('UserID', $USERID)->firstOrFail();
      $user->perkiraan()->sync($request->Perkiraan);
      DB::commit();
      return true;
    } catch (QueryException $e) {
      DB::rollback();
      return abort(400, $e->getMessage());
    }
  }

  public function createNeraca($perkiraan, $bulan, $tahun){
    $this->model->dbneraca->create([
      'Perkiraan' => $perkiraan,
      'Bulan' => $bulan,
      'Tahun' => $tahun,
      'Devisi' => '01',
      'DK' => 0,
      'Valas' => '',
    ]);
  }
}

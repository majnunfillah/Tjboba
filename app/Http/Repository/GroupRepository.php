<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\GroupInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupRepository extends BaseRepository implements GroupInterface
{
  public function __construct()
  {
    parent::__construct();
  }

  public function getAllGroup()
  {
    try {
      return $this->queryModel('dbgroup')->get();
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function getGroupByKodeGroup($KodeGrp)
  {
    try {
      return $this->queryModel('dbgroup')->where('KODEGRP', $KodeGrp)->firstOrNew();
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function storeGroup(Request $request)
  {
    $request->validate([
      'KODEGRP' => ['required', 'string', 'min:2', 'max:15'],
      'NAMA' => ['required', 'string', 'min:5', 'max:40']
    ]);
    DB::beginTransaction();
    try {
      $this->queryModel('dbgroup')->create([
        'KODEGRP' => $request->KODEGRP,
        'NAMA' => $request->NAMA
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function updateGroup(Request $request, $KodeGrp)
  {
    $request->validate([
      'NAMA' => ['required', 'string', 'min:5', 'max:40']
    ]);
    DB::beginTransaction();
    try {
      $this->queryModel('dbgroup')->where('KODEGRP', $KodeGrp)->update([
        'NAMA' => $request->NAMA
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function destroyGroup($KodeGrp)
  {
    DB::beginTransaction();
    try {
      $this->queryModel('dbgroup')->where('KODEGRP', $KodeGrp)->delete();
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function getAllSubGroupByGroup($KodeGrp)
  {
    try {
      return $this->queryModel('dbsubgroup')->from('dbsubgroup as a')
        ->select(
          'a.*',
          'p1.Keterangan as KeteranganPerkPers',
          'p2.Keterangan as KeteranganPerkH',
          'p3.Keterangan as KeteranganPerkPPN',
          'p4.Keterangan as KeteranganPerkBiaya',
        )
        ->where('KodeGrp', $KodeGrp)
        ->leftjoin('dbperkiraan as p1', 'p1.Perkiraan', '=', 'a.PerkPers')
        ->leftjoin('dbperkiraan as p2', 'p2.Perkiraan', '=', 'a.PerkH')
        ->leftjoin('dbperkiraan as p3', 'p3.Perkiraan', '=', 'a.PerkPPN')
        ->leftjoin('dbperkiraan as p4', 'p4.Perkiraan', '=', 'a.PerkBiaya')
        ->get();
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function storeSubGroup(Request $request, $KodeGrp)
  {
    $request->validate([
      'KodeSubGrp' => ['required', 'string', 'min:1', 'max:10'],
      'NamaSubGrp' => ['required', 'string', 'min:1', 'max:50'],
      'PerkPers' => ['required', 'string', 'min:1', 'max:25'],
      'PerkH' => ['required', 'string', 'min:1', 'max:25'],
      'PerkPPN' => ['required', 'string', 'min:1', 'max:25'],
      'PerkBiaya' => ['required', 'string', 'min:1', 'max:30']
    ]);
    DB::beginTransaction();
    try {
      $this->queryModel('dbsubgroup')->create([
        'KodeGrp' => $KodeGrp,
        'KodeSubGrp' => $request->KodeSubGrp,
        'NamaSubGrp' => $request->NamaSubGrp,
        'PerkPers' => $request->PerkPers,
        'PerkH' => $request->PerkH,
        'PerkPPN' => $request->PerkPPN,
        'PerkBiaya' => $request->PerkBiaya
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function updateSubGroup(Request $request, $KodeGrp, $KodeSubGrp)
  {
    $request->validate([
      'NamaSubGrp' => ['required', 'string', 'min:1', 'max:50'],
      'PerkPers' => ['required', 'string', 'min:1', 'max:25'],
      'PerkH' => ['required', 'string', 'min:1', 'max:25'],
      'PerkPPN' => ['required', 'string', 'min:1', 'max:25'],
      'PerkBiaya' => ['required', 'string', 'min:1', 'max:30']
    ]);
    DB::beginTransaction();
    try {
      $this->queryModel('dbsubgroup')->where('KodeGrp', $KodeGrp)->where('KodeSubGrp', $KodeSubGrp)->update([
        'NamaSubGrp' => $request->NamaSubGrp,
        'PerkPers' => $request->PerkPers,
        'PerkH' => $request->PerkH,
        'PerkPPN' => $request->PerkPPN,
        'PerkBiaya' => $request->PerkBiaya
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function deleteSubGroup($KodeGrp, $KodeSubGrp)
  {
    DB::beginTransaction();
    try {
      $this->queryModel('dbsubgroup')
        ->where('KodeGrp', $KodeGrp)
        ->where('KodeSubGrp', $KodeSubGrp)
        ->delete();
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function getAllDepartemenByGroupAndSubGroup($KodeGrp, $KodeSubGrp)
  {
    try {
      return $this->queryModel('dbsubgroupjnstambah')->from('dbsubgroupjnstambah as a')
        ->select(
          'a.*',
          'dp.NMDEP'
        )
        ->where('KodeGrp', $KodeGrp)
        ->leftjoin('dbdepart as dp', 'dp.KDDEP', '=', 'a.Keterangan')
        ->get();
    } catch (QueryException $ex) {
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function storeDepartemen(Request $request, $KodeGrp, $KodeSubGrp){
    $request->validate([
      'Departemen' => ['required', 'numeric']
    ]);

    DB::beginTransaction();
    try {
      $count = $this->queryModel('dbsubgroupjnstambah')->where('KodeGrp', $KodeGrp)->where('KodeSubGrp', $KodeSubGrp)->count();
      $this->queryModel('dbsubgroupjnstambah')->create([
        'KodeGrp' => $KodeGrp,
        'KodeSubGrp' => $KodeSubGrp,
        'Keterangan' => $request->Departemen,
        'Urut' => $count + 1
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function updateDepartemen(Request $request, $KodeGrp, $KodeSubGrp, $kodeDep){
    $request->validate([
      'Departemen' => ['required', 'numeric']
    ]);

    DB::beginTransaction();
    try {
      $this->queryModel('dbsubgroupjnstambah')->where('KodeGrp', $KodeGrp)->where('KodeSubGrp', $KodeSubGrp)
      ->where('Keterangan',$kodeDep)
      ->update([
        'Keterangan' => $request->Departemen,
      ]);
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

  public function deleteDepartemen($KodeGrp, $KodeSubGrp, $kodeDep){
    DB::beginTransaction();
    try {
      $this->queryModel('dbsubgroupjnstambah')->where('KodeGrp', $KodeGrp)->where('KodeSubGrp', $KodeSubGrp)
      ->where('Keterangan',$kodeDep)
      ->delete();
      DB::commit();
      return true;
    } catch (QueryException $ex) {
      DB::rollBack();
      return abort(501, 'Error : ' . $ex->getMessage() . '. di Baris : ' . $ex->getLine());
    }
  }

}

<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\DBFLPASSInterface;
use App\Http\Requests\DBFLPASSRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DBFLPASSRepository extends BaseRepository implements DBFLPASSInterface
{

  public function __construct()
  {
    parent::__construct(['DBFLMENU', 'DBFLPASS', 'DBMENU']);
  }

  public function getAllUser()
  {
    return $this->model->{$this->db}
      ->select("{$this->db}.*", "jab.NamaJab", "dep.NMDEP")
      ->leftjoin('DBJABATAN as jab', 'jab.KODEJAB', '=', $this->db . ".KodeJab")
      ->leftjoin('DBDEPART as dep', 'dep.KDDEP', '=', $this->db . ".kodeBag")
      ->orderBy('USERID', 'ASC')
      ->get();
  }

  public function createKaryawan(DBFLPASSRequest $request)
  {
    DB::beginTransaction();
    try {
      $validated = $request->validated();
      unset($validated['UID']);
      $validated['UID2'] = base64_encode($validated['UID2']);
      DB::insert("insert into [DBFLPASS] ([USERID], [FullName], [KodeJab], [kodeBag], [TINGKAT], [STATUS], [HOSTID], [IPAddres], [KodeKasir], [Kodegdg], [keynik]) values ('$validated[USERID]', '$validated[FullName]', '$validated[KodeJab]', '$validated[kodeBag]', '$validated[TINGKAT]', '$validated[STATUS]', '$validated[HOSTID]', '$validated[IPAddres]', '$validated[KodeKasir]', '$validated[Kodegdg]', '$validated[keynik]')");
      DB::commit();
      return true;
    } catch (QueryException $th) {
      DB::rollback();
      return abort(500, $th->getMessage());
    }
  }

  public function updateKaryawan(DBFLPASSRequest $request)
  {
    DB::beginTransaction();
    try {
      $validated = $request->validated();
      unset($validated['UID']);
      $validated['UID2'] = base64_encode($request->UID2);
      DB::statement("update [DBFLPASS] set [USERID] = '$validated[USERID]', [FullName] = '$validated[FullName]', [KodeJab] = '$validated[KodeJab]', [kodeBag] = '$validated[kodeBag]', [TINGKAT] = '$validated[TINGKAT]', [STATUS] = '$validated[STATUS]', [HOSTID] = '$validated[HOSTID]', [IPAddres] = '$validated[IPAddres]', [KodeKasir] = '$validated[KodeKasir]', [Kodegdg] = '$validated[Kodegdg]', [keynik] = '$validated[keynik]' where [USERID] = '$validated[USERID]'");
      DB::commit();
      return true;
    } catch (QueryException $th) {
      DB::rollback();
      return abort(500, $th->getMessage());
    }
  }

  public function deleteKaryawan($USERID)
  {
    DB::beginTransaction();
    try {
      DB::delete("delete from [DBFLPASS] where [USERID] = '$USERID'");
      DB::commit();
      return true;
    } catch (QueryException $th) {
      DB::rollback();
      return abort(500, $th->getMessage());
    }
  }

  public function updatePermission($data, $USERID)
  {
    DB::beginTransaction();
    try {
      if (gettype($this->model->dbflpass) == 'string') {
        $this->model('dbflpass');
      }
      $KODEMENU = [];
      $pivot = [];
      foreach ($data as $value) {
        $explodes = explode('-', $value);
        array_push($pivot, (object) ['permission' => $explodes[0], 'KODEMENU' => $explodes[1]]);
        array_push($KODEMENU, $explodes[1]);
      }
      $KODEMENU = array_unique($KODEMENU);

      $attributes = [];
      foreach ($KODEMENU as $key => $data) {
        $temp = [];
        foreach ($pivot as $value) {
          if ($value->KODEMENU == $data && $value->permission != 'basic') {
            $temp += [$value->permission => 1];
          }
        }
        $temp += ['L1' => $data];
        if (!array_key_exists('HASACCESS', $temp)) {
          $temp += ['HASACCESS' => 0];
        }
        if (!array_key_exists('ISTAMBAH', $temp)) {
          $temp += ['ISTAMBAH' => 0];
        }
        if (!array_key_exists('ISKOREKSI', $temp)) {
          $temp += ['ISKOREKSI' => 0];
        }
        if (!array_key_exists('ISHAPUS', $temp)) {
          $temp += ['ISHAPUS' => 0];
        }
        if (!array_key_exists('ISCETAK', $temp)) {
          $temp += ['ISCETAK' => 0];
        }
        if (!array_key_exists('ISEXPORT', $temp)) {
          $temp += ['ISEXPORT' => 0];
        }
        if (!array_key_exists('IsBatal', $temp)) {
          $temp += ['IsBatal' => 0];
        }
        if (!array_key_exists('IsOtorisasi1', $temp)) {
          $temp += ['IsOtorisasi1' => 0];
        }
        if (!array_key_exists('IsOtorisasi2', $temp)) {
          $temp += ['IsOtorisasi2' => 0];
        }
        if (!array_key_exists('IsOtorisasi3', $temp)) {
          $temp += ['IsOtorisasi3' => 0];
        }
        if (!array_key_exists('IsOtorisasi4', $temp)) {
          $temp += ['IsOtorisasi4' => 0];
        }
        if (!array_key_exists('IsOtorisasi5', $temp)) {
          $temp += ['IsOtorisasi5' => 0];
        }
        if (!array_key_exists('TIPE', $temp)) {
          $temp += ['TIPE' => 'STK'];
        }
        array_push($attributes, $temp);
      }
      $user = $this->model->dbflpass->where('USERID', $USERID)->firstOrFail();

       
      $user->dbflmenu()->sync($attributes);
      DB::commit();
      return true;
    } catch (QueryException $th) {
      DB::rollback();
      return abort(500, $th->getMessage());
    }
  }


  public function getAllMenu($USERID)
  {
    return DB::select("select * from [DBMENU] left join [DBFLMENU] as [a] on [a].[L1] = [KODEMENU] and [USERID] = '{$USERID}' order by [KODEMENU] asc");
  }
  
}

<?php

namespace App\Http\Repository\Task;

use App\Http\Requests\DBFLPASSRequest;

interface DBFLPASSInterface extends BaseInterface
{
  public function getAllUser();
  public function createKaryawan(DBFLPASSRequest $request);
  public function updateKaryawan(DBFLPASSRequest $request);
  public function deleteKaryawan($USERID);
  public function updatePermission($data, $USERID);
}
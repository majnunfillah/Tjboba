<?php
namespace App\Http\Repository\Task;

use Illuminate\Http\Request;

interface GroupInterface
{
  public function getAllGroup();
  public function getGroupByKodeGroup($KodeGrp);
  public function storeGroup(Request $request);
  public function updateGroup(Request $request, $KodeGrp);
  public function destroyGroup($KodeGrp);
  public function getAllSubGroupByGroup($KodeGrp);
  public function storeSubGroup(Request $request, $KodeGrp);
  public function updateSubGroup(Request $request, $KodeGrp, $KodeSubGrp);
  public function deleteSubGroup($KodeGrp, $KodeSubGrp);
  public function getAllDepartemenByGroupAndSubGroup($KodeGrp, $KodeSubGrp);
  public function storeDepartemen(Request $request, $KodeGrp, $KodeSubGrp);
  public function updateDepartemen(Request $request, $KodeGrp, $KodeSubGrp, $kodeDep);
  public function deleteDepartemen($KodeGrp, $KodeSubGrp, $kodeDep);
}
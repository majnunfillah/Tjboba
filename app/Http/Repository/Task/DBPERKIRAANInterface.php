<?php

namespace App\Http\Repository\Task;

use App\Http\Requests\PerkiraanRequest;
use Illuminate\Http\Request;

interface DBPERKIRAANInterface
{
  public function getAccessCOA($userId);
  public function updateAccessCOA(Request $request, $userId);
  public function getPerkiraan($perkiraan);
  public function storePerkiraan(PerkiraanRequest $request);
  public function updatePerkiraan(PerkiraanRequest $request, $Perkiraan);
  public function deletePerkiraan($Perkiraan);
  public function getAllPerkiraan();
  public function getSaldoAwal($perkiraan, $bulan, $tahun);
  public function setSaldoAwal(Request $request, $perkiraan);
  public function getBudget($perkiraan, $tahun);
  public function setBudget(Request $request, $perkiraan);
}

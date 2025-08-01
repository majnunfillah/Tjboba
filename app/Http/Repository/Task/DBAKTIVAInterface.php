<?php

namespace App\Http\Repository\Task;

use App\Http\Requests\AktivaRequest;
use Illuminate\Http\Request;

interface DBAKTIVAInterface
{
  public function getAktiva($perkiraan, $devisi);
  public function getAllAktiva($devisi, $tipeAktiva);
  public function detailAktiva($kodeAktiva);
  public function storeAktiva(AktivaRequest $aktivaRequest);
  public function updateAktiva(AktivaRequest $aktivaRequest, $perkiraan, $devisi);
  public function deleteAktiva($perkiraan, $devisi);
  public function getSaldoAwal($perkiraan, $devisi, $periode);
  public function setSaldoAwal(Request $request, $perkiraan, $devisi, $periode);
}

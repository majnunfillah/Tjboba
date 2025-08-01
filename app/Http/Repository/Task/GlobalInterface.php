<?php

namespace App\Http\Repository\Task;

use Illuminate\Http\Request;

interface GlobalInterface
{
  public function getKelompokKasOrBank($type = 'KAS', $query = null);
  public function storeKelompokKasOrBank($perkiraan, $oldPerkiraan = null, $type = 'KAS');
  public function deleteKelompokKasOrBank($perkiraan, $type = 'KAS');
  public function getKelompokAktiva();
  public function storeKelompokAktiva(Request $request, $oldPerkiraan = null);
  public function deleteKelompokAktiva($perkiraan);
  public function getNomorSPK();
  public function getCustomerHutang($JENIS = NULL);
}
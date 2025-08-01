<?php

namespace App\Http\Repository\Task;

use App\Http\Requests\NomorTransaksiRequest;
use App\Http\Requests\PerusahaanRequest;

interface DBPERUSAHAANInterface
{
  public function updatePerusahaan(PerusahaanRequest $request, $id);
  public function updateNomor(NomorTransaksiRequest $request);
}

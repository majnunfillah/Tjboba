<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Database\QueryException;
use App\Http\Requests\PerusahaanRequest;
use App\Http\Repository\Task\DBPERUSAHAANInterface;
use App\Http\Requests\NomorTransaksiRequest;
use Illuminate\Support\Facades\Storage;

class DBPERUSAHAANRepository extends BaseRepository implements DBPERUSAHAANInterface
{
  public function __construct()
  {
    parent::__construct(['DBPERUSAHAAN', 'DBNOMOR']);
  }

  public function updatePerusahaan(PerusahaanRequest $request, $id)
  {
    DB::beginTransaction();
    try {
      $perusahaan = $this->firstOrNew();

      $validated = $request->validated();
      $validated = deletFiles($validated, $perusahaan, ['LOGO_PATH', 'TTD_PATH']);
      $validated = storeFile($validated, 'berkas/perusahaan', ['LOGO_PATH', 'TTD_PATH']);
      if ($perusahaan->update($validated)) {
        DB::commit();
        return true;
      }
    } catch (QueryException $e) {
      DB::rollback();
      return abort(501, $e);
    }
  }

  public function updateNomor(NomorTransaksiRequest $request)
  {
    DB::beginTransaction();
    try {
      $nomor = $this->firstOrNew();

      $validated = $request->validated();
      if ($nomor->update($validated)) {
        DB::commit();
        return true;
      }
    } catch (QueryException $e) {
      DB::rollback();
      return abort(501, $e);
    }
  }
}

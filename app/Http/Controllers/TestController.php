<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\GlobalInterface;
use App\Http\Services\CustomDataTable;
use App\Models\DBBARANG;
use App\Models\DBFLMENU;
use App\Models\DBFLPASS;
use App\Models\DBMENU;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    private $globalRepository;

    public function __construct(GlobalInterface $globalRepository)
    {
        $this->globalRepository = $globalRepository;
    }

    public function __invoke(Request $request)
    {
        DB::beginTransaction();

        try {
            dd('test', DB::connection('sqlsrv')->getDatabaseName(), DB::connection('sqlsrv')->table('dbMenu')->get());

            $type = 'BANK';
            $query = null;
            $aktiva = $this->globalRepository->queryModel('dbperkiraan')
            ->select('Perkiraan', 'Keterangan')
            ->with('kelompok_kas')
            ->whereHas('kelompok_kas', function ($q) use ($type) {
                $q->where('Kode', $type);
            })
            ->when($query !== NULL, function ($q) use ($query) {
                return $q->where('Keterangan', 'like', '%' . $query . '%');
            });
            // DB::enableQueryLog();
            // $aktiva = DB::select("select * from dbperkiraan where Perkiraan in (select Perkiraan from dbposthutpiut where Kode = 'BANK')");
            // dd($aktiva);
            // ddd($aktiva->toSql(), "select [Perkiraan], [Keterangan] from [DBPERKIRAAN] where exists (select * from [DBPOSTHUTPIUT] where [DBPERKIRAAN].[Perkiraan] = [DBPOSTHUTPIUT].[Perkiraan] and [Kode] = ?)");
            return CustomDataTable::init()->of($aktiva)->makeJson();
            // DB::commit();
            // return true;
        } catch (QueryException $th) {
            DB::rollback();
            return abort(400, $th->getMessage());
        }
    }
}

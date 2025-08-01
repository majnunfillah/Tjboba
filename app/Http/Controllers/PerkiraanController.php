<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Anam\PhantomMagick\Converter;
use App\Http\Services\CustomDataTable;
use App\Http\Requests\PerkiraanRequest;
use App\Http\Repository\Task\DBPERKIRAANInterface;

/**
 * @method getAccessCOA($userid)
 */
class PerkiraanController extends Controller
{
    private $perkiraanRepository;

    public function __construct(DBPERKIRAANInterface $perkiraanRepository)
    {
        $this->perkiraanRepository = $perkiraanRepository;
    }

    public function index()
    {
        $access = auth()->user()->getPermissionsName('01001001');
        if (request()->ajax()) {
            // ini_set('execution_time', 300);
            return CustomDataTable::init()
                ->of($this->perkiraanRepository->getAllPerkiraan())
                ->mapData(function ($data) use ($access) {
                    if ($data->Kelompok == 0) {
                        $data->Kelompok = 'Aktiva';
                    } else if ($data->Kelompok == 1) {
                        $data->Kelompok = 'Kewajiban';
                    } else if ($data->Kelompok == 2) {
                        $data->Kelompok = 'Modal';
                    } else if ($data->Kelompok == 3) {
                        $data->Kelompok = 'Pendapatan';
                    } else {
                        $data->Kelompok = 'Biaya';
                    }

                    if ($data->Tipe == 0) {
                        $data->Tipe = 'General';
                    } else {
                        $data->Tipe = 'Detail';
                    }

                    if ($data->DK == 0) {
                        $data->DK = "DEBET";
                    }else{
                        $data->DK = "KREDIT";
                    }
                    
                    if($data->KodeAK != null){
                        $data->KodeAK = $data->aruskas->NamaAK;
                    }
                    if($data->KodeSAK != null){
                        $data->KodeSAK = $data->aruskasdet->NamaSubAK;
                    }
                    
                    if (in_array('ISTAMBAH', $access)) {
                        $data->canAdd = true;
                    }

                    return $data;
                })
                ->apply()
                ->addColumn('action', function ($data) use ($access) {
                    $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                    <div class="notification-container close-button-container">';
                    if (in_array('ISKOREKSI', $access)) {
                        if($data->Tipe == 'Detail' ){
                            // $url = route('master-data.master-accounting.get-saldo-awal', ['perkiraan' => $data->Perkiraan]);
                            $html .= "<button class='btn btn-warning btn-sm mr-1 btnGetSaldoAwal' data-perkiraan='{$data->Perkiraan}'><i class='fa fa-list-alt mr-1'></i>Saldo Awal</button>";
                        }
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnGetBudget' data-perkiraan='{$data->Perkiraan}'><i class='fa fa-money-check mr-1'></i>Budget</button>";
                        // $url = route('berkas.set-pemakai-coa.update', ['USERID' => $data->USERID]);
                        // $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditAccessCOA' data-url='{$url}' data-userid='{$data->USERID}'><i class='fa fa-user-shield mr-1'></i>Akses COA</button>";

                        $url = route('master-data.master-accounting.perkiraan.update', ['perkiraan' => $data->Perkiraan]);
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditPerkiraan' data-perkiraan='{$data->Perkiraan}' data-url='{$url}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $access)) {
                        $url = route('master-data.master-accounting.perkiraan.destroy', ['perkiraan' => $data->Perkiraan]);
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->Perkiraan}' data-key='{$data->Keterangan}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    return $html;
                })
                ->done();
        }
        return view('master_data.master_accounting.perkiraan', compact('access'));
    }

    public function store(Request $request)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('ISTAMBAH', $access)) {
                if ($this->perkiraanRepository->storePerkiraan(app()->make(PerkiraanRequest::class))) {
                    return $this->setResponseData(['datatable' => 'datatableMain']);
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function update(Request $request, $Perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('ISKOREKSI', $access)) {
                if ($this->perkiraanRepository->updatePerkiraan(app()->make(PerkiraanRequest::class), $Perkiraan)) {
                    return $this->setResponseData(['datatable' => 'datatableMain']);
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function destroy($Perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('ISHAPUS', $access)) {
                if ($this->perkiraanRepository->deletePerkiraan($Perkiraan)) {
                    return $this->setResponseData(['datatable' => 'datatableMain'], "Berhasil menghapus data");
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function getSaldoAwal($perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('HASACCESS', $access)) {
                if($periode = $this->perkiraanRepository->queryModel('dbperiode')->where('USERID', '=', auth()->user()->USERID)->first()){
                    if($check = $this->perkiraanRepository->queryModel('dbperkiraan')->where('Perkiraan', $perkiraan)->first()){
                        if($check->Tipe == 0){
                            return abort(403, 'Perkiraan General tidak dapat diisi saldo awal');
                        }
                    }
                    if($periode->BULAN != 01){
                        return $this->setResponseError('Set Saldo Awal harus dilakukan pada bulan Januari');
                    }
                    return $this->perkiraanRepository->getSaldoAwal($perkiraan, $periode->BULAN, $periode->TAHUN);
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function setSaldoAwal(Request $request, $perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('ISKOREKSI', $access)) {
                if($this->perkiraanRepository->setSaldoAwal($request, $perkiraan)){
                    return $this->setResponseSuccess('Saldo Awal berhasil diset');
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function getBudget($perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('HASACCESS', $access)) {
                if($periode = $this->perkiraanRepository->queryModel('dbperiode')->where('USERID', '=', auth()->user()->USERID)->first()){
                    return $this->perkiraanRepository->getBudget($perkiraan, $periode->TAHUN);
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function setBudget(Request $request, $perkiraan)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('01001001');
            if (in_array('ISKOREKSI', $access)) {
                if($this->perkiraanRepository->setBudget($request, $perkiraan)){
                    return $this->setResponseSuccess('Budget berhasil diset');
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
    }

    public function getPerkiraan($perkiraan){

        if(request()->ajax()){
            return $this->perkiraanRepository->getPerkiraan($perkiraan);
        }
        return abort(501, 'Data tidak ditemukan');
    }

    public function getAccessCOA($userId)
    {
        return $this->perkiraanRepository->getAccessCOA($userId);
    }

    public function updateCOA(Request $request, $userId)
    {
        if (request()->ajax()) {
            $access = auth()->user()->getPermissionsName('0004');
            if (in_array('ISKOREKSI', $access)) {
                if ($this->perkiraanRepository->updateAccessCOA($request, $userId)) {
                    return $this->setResponseSuccess();
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
        return abort(404, 'Halaman tidak ditemukan');
    }
}

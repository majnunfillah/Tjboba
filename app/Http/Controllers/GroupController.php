<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\GroupInterface;
use App\Http\Services\CustomDataTable;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    private $groupRepository;
    private $access;

    public function __construct(GroupInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->middleware(function ($request, $next) {
            $this->access = auth()->user()->getPermissionsName('01002015');
            return $next($request);
        });
    }

    public function index(){
        if (request()->ajax()) {
            return CustomDataTable::init()
            ->of($this->groupRepository->getAllGroup())
            ->apply()
            ->addColumn('action', function ($data) {
                $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                <div class="notification-container close-button-container">';
                if (in_array('ISKOREKSI', $this->access)) {
                    $html .= "<button class='btn btn-success btn-sm mr-1 btnSubGroup' data-group='{$data->KODEGRP}'><i class='fa fa-object-group mr-1'></i>Sub Group</button>";

                    $url = route('master-data.master-bahan-dan-barang.group.update', ['group' => $data->KODEGRP]);
                    $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditGroup' data-group='{$data->KODEGRP}' data-url='{$url}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                }
                if (in_array('ISHAPUS', $this->access)) {
                    $url = route('master-data.master-bahan-dan-barang.group.destroy', ['group' => $data->KODEGRP]);
                    $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->KODEGRP}' data-key='{$data->KODEGRP}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                }
                $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                return $html;
            })
            ->done();
        }

        return view('master_data.master-bahan-barang.group');
    }

    public function getGroup($KodeGrp)
    {
        return $this->groupRepository->getGroupByKodeGroup($KodeGrp);
    }

    public function store(Request $request){
        $this->requestAjax($this->access, 'ISTAMBAH');
        if ($this->groupRepository->storeGroup($request)) {
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    public function update(Request $request, $KodeGrp){
        $this->requestAjax($this->access, 'ISKOREKSI');
        if ($this->groupRepository->updateGroup($request, $KodeGrp)) {
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    public function destroy($KodeGrp)
    {
        return $this->groupRepository->destroyGroup($KodeGrp);
    } 

    public function getSubGroup($KodeGrp){
        if (request()->ajax()) {
            return CustomDataTable::init()
            ->of($this->groupRepository->getAllSubGroupByGroup($KodeGrp))
            ->apply()
            ->addColumn('action', function ($data) {
                $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                <div class="notification-container close-button-container">';
                if (in_array('ISKOREKSI', $this->access)) {
                    $html .= "<button class='btn btn-success btn-sm mr-1 btnDepartemen'><i class='fa fa-building mr-1'></i>Departemen</button>";

                    $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSubGroup'><i class='fa fa-pen mr-1'></i>Edit</button>";
                }
                if (in_array('ISHAPUS', $this->access)) {
                    $url = route('master-data.master-bahan-dan-barang.sub-group.destroy', ['group' => $data->KodeGrp, 'subgroup' => $data->KodeSubGrp]);
                    $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-datatable='datatableSubGroup' data-url='{$url}' data-key='{$data->NamaSubGrp}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                }
                $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                return $html;
            })
            ->done();
        }

        return view('master_data.master-bahan-barang.group');
    }
    
    public function storeSubGroup(Request $request, $KodeGrp){
        $this->requestAjax($this->access, 'ISTAMBAH');
        if ($this->groupRepository->storeSubGroup($request, $KodeGrp)) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }
    
    public function updateSubGroup(Request $request, $KodeGrp, $KodeSubGrp){
        $this->requestAjax($this->access, 'ISKOREKSI');
        if ($this->groupRepository->updateSubGroup($request, $KodeGrp, $KodeSubGrp)) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    public function deleteSubGroup($KodeGrp, $KodeSubGrp){
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->groupRepository->deleteSubGroup($KodeGrp, $KodeSubGrp)) {
            return $this->setResponseData(['datatable' => 'datatableSubGroup']);
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    public function getDepartemen($KodeGrp, $KodeSubGrp){
        if (request()->ajax()) {
            return CustomDataTable::init()
            ->of($this->groupRepository->getAllDepartemenByGroupAndSubGroup($KodeGrp,$KodeSubGrp))
            ->apply()
            ->addColumn('action', function ($data) {
                $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                <div class="notification-container close-button-container">';
                if (in_array('ISKOREKSI', $this->access)) {
                    $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditDepartemen'><i class='fa fa-pen mr-1'></i>Edit</button>";
                }
                if (in_array('ISHAPUS', $this->access)) {
                    $url = route('master-data.master-bahan-dan-barang.sub-group.departemen.destroy', ['group' => $data->KodeGrp, 'subgroup' => $data->KodeSubGrp, 'KodeDepartemen' => $data->Keterangan]);
                    $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-datatable='datatableDepartemen' data-url='{$url}' data-key='{$data->NMDEP}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                }
                $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                return $html;
            })
            ->done();
        }

        return view('master_data.master-bahan-barang.group');
    }

    public function storeDepartemen(Request $request, $KodeGrp, $KodeSubGrp){
        $this->requestAjax($this->access, 'ISTAMBAH');
        if ($this->groupRepository->storeDepartemen($request, $KodeGrp, $KodeSubGrp)) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }
    
    public function updateDepartemen(Request $request, $KodeGrp, $KodeSubGrp, $kodeDep){
        $this->requestAjax($this->access, 'ISKOREKSI');
        if ($this->groupRepository->updateDepartemen($request, $KodeGrp, $KodeSubGrp, $kodeDep)) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    public function deleteDepartemen($KodeGrp, $KodeSubGrp, $kodeDep){
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->groupRepository->deleteDepartemen($KodeGrp, $KodeSubGrp, $kodeDep)) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }
}

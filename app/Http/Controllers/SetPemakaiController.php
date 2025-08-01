<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\DBFLPASSInterface;
use App\Http\Requests\DBFLPASSRequest;
use App\Http\Services\CustomDataTable;
use Illuminate\Http\Request;

class SetPemakaiController extends Controller
{
    private $userRepository;

    public function __construct(DBFLPASSInterface $userRepository)
    {
        $this->userRepository = $userRepository->model('dbflpass');
    }

    public function index()
    {
        $access = auth()->user()->getPermissionsName('0004');
        if (request()->ajax()) {
            return CustomDataTable::init()
                ->of($this->userRepository->getAllUser())
                ->mapData(function ($data) use ($access) {
                    if ($data->TINGKAT == 2) {
                        $data->TINGKAT = 'Administrator';
                    } else if ($data->TINGKAT == 1) {
                        $data->TINGKAT = 'Supervisor';
                    } else {
                        $data->TINGKAT = 'User';
                    }

                    if ($data->STATUS == 1) {
                        $data->STATUS = 'Online';
                    } else {
                        $data->STATUS = 'Offline';
                    }

                    if ($data->HOSTID != null && $data->IPAddres != null) {
                        $data->online_from = "$data->HOSTID ($data->IPAddres)";
                    } else {
                        $data->online_from = NULL;
                    }

                    if ($data->KodeKasir == "") {
                        $data->KodeKasir = NULL;
                    }

                    if ($data->Kodegdg == "") {
                        $data->Kodegdg = NULL;
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
                        $url = route('berkas.set-pemakai.update', ['USERID' => $data->USERID]);
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditPermission' data-url='{$url}' data-userid='{$data->USERID}'><i class='fa fa-user-shield mr-1'></i>Permission</button>";
                        $url = route('berkas.set-pemakai-coa.update', ['USERID' => $data->USERID]);
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditAccessCOA' data-url='{$url}' data-userid='{$data->USERID}'><i class='fa fa-user-shield mr-1'></i>Akses COA</button>";
                        $url = route('berkas.set-pemakai-karyawan.update', ['USERID' => $data->USERID]);
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditKaryawan' data-userid='{$data->USERID}' data-url='{$url}'><i class='fa fa-user-edit mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $access)) {
                        $url = route('berkas.set-pemakai-karyawan.delete', ['USERID' => $data->USERID]);
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->USERID}' data-key='{$data->USERID}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    return $html;
                })
                ->done();
        }
        return view('berkas.set_pemakai.index', compact('access'));
    }

    public function update(Request $request, $USERID)
    {
        if (request()->ajax()) {

            $access = auth()->user()->getPermissionsName('0004');
            if (in_array('ISKOREKSI', $access)) {
                if ($this->userRepository->updatePermission($request->data, $USERID)) {
                    return $this->setResponseData(['datatable' => 'datatableMain'], "Berhasil set permissioon");
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function createKaryawan(Request $request)
    {
        if ($request->ajax()) {
            // dd($request->all());
            $access = auth()->user()->getPermissionsName('0004');
            if (in_array('ISTAMBAH', $access)) {
                if ($this->userRepository->createKaryawan(app()->make(DBFLPASSRequest::class))) {
                    return $this->setResponseData(['datatable' => 'datatableMain'], "Berhasil menambahkan data");
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function updateKaryawan(Request $request, $USERID)
    {
        if ($request->ajax()) {
            $access = auth()->user()->getPermissionsName('0004');
            if (in_array('ISKOREKSI', $access)) {
                if ($this->userRepository->updateKaryawan(app()->make(DBFLPASSRequest::class))) {
                    return $this->setResponseData(['datatable' => 'datatableMain'], "Berhasil mengubah data");
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function deleteKaryawan($USERID)
    {
        if (request()->ajax()) {

            $access = auth()->user()->getPermissionsName('0004');
            if (in_array('ISHAPUS', $access)) {
                if ($this->userRepository->deleteKaryawan($USERID)) {
                    return $this->setResponseData(['datatable' => 'datatableMain'], "Berhasil menghapus data");
                }
            }
            return abort(403, 'Anda tidak memiliki izin ini');
        }
        return abort(404, 'Halaman tidak ditemukan');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\DBAKTIVAInterface;
use App\Http\Requests\AktivaRequest;
use App\Http\Services\CustomDataTable;
use Illuminate\Http\Request;

class AktivaController extends Controller
{

    private $aktivaRepository;
    private $access;

    public function __construct(DBAKTIVAInterface $aktivaRepository)
    {
        $this->aktivaRepository = $aktivaRepository;
        $this->middleware(function ($request, $next) {
            $this->access = auth()->user()->getPermissionsName('01001001');
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd($this->aktivaRepository->queryModel('dbposthutpiut'));
        if (in_array('HASACCESS', $this->access)) {
            if (request()->ajax()) {
                $devisi = request()->get('devisi');
                $tipeAktiva = request()->get('tipeAktiva');
                $datatable = CustomDataTable::init()
                    ->of($this->aktivaRepository->getAllAktiva($devisi, $tipeAktiva))
                    ->mapData(function ($data) {
                        $data->Tanggal = date('d/m/Y', strtotime($data->Tanggal));
                        $data->Keterangan = trim($data->Keterangan);
 
                        if(in_array('ISTAMBAH', $this->access)){
                            $data->canAdd = true;
                        }
                        // dd($this->aktivaRepository->detailAktiva($data->KodeAktiva)[0]);
                        // $data->btnExpand = '<button class="btn btn-success btn-sm btn-expand-aktiva"><i class="fa fa-plus"></i></button>';
                        return $data;
                    })
                    ->apply();
                    if(request()->download === FALSE){
                        $datatable->addColumn('table_expand', function ($data) {
                            return view('components.master_data.master_accounting.aktiva.expand_table', [
                                'data' => $this->aktivaRepository->detailAktiva($data->KodeAktiva)
                            ])->render();
                        });
                    }
                    return $datatable->addColumn('action', function ($data) {
                        $button = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';
                        if (in_array('ISKOREKSI', $this->access)) {
                            $url = route('master-data.master-accounting.aktiva.saldo-awal', ['aktiva' => $data->KodeAktiva, 'devisi' => $data->Devisi]);
                            $button .= "<button class='btn btn-warning btn-sm mr-1 btnSaldoAwal' data-perkiraan='{$data->KodeAktiva}' data-devisi='{$data->Devisi}' data-url='{$url}'><i class='fa fa-money-bill mr-1'></i>Saldo Awal</button>";
                            $url = route('master-data.master-accounting.aktiva.update', ['aktiva' => $data->KodeAktiva, 'devisi' => $data->Devisi]);
                            $button .= "<button class='btn btn-warning btn-sm mr-1 btnEditAktiva' data-perkiraan='{$data->KodeAktiva}' data-devisi='{$data->Devisi}' data-url='{$url}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                        }
                        if (in_array('ISHAPUS', $this->access)) {
                            $url = route('master-data.master-accounting.aktiva.destroy', ['aktiva' => $data->KodeAktiva, 'devisi' => $data->Devisi]);
                            $button .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->KodeAktiva}' data-key='{$data->KodeAktiva}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                        }
                        $button .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                        return $button;
                    })->done();
                // return $this->aktivaRepository->getAllAktiva($devisi, $tipeAktiva);
            }
            return view('master_data.master_accounting.aktiva');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->requestAjax($this->access, 'ISTAMBAH');
        if($this->aktivaRepository->storeAktiva(app()->make(AktivaRequest::class))){
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $perkiraan, $devisi)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        if($this->aktivaRepository->updateAktiva(app()->make(AktivaRequest::class), $perkiraan, $devisi)){
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($perkiraan, $devisi)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        if($this->aktivaRepository->deleteAktiva($perkiraan, $devisi)){
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
    }

    public function getSaldoAwal($perkiraan, $devisi){
        $this->requestAjax();
        $periode = $this->aktivaRepository->queryModel('dbperiode')->where('USERID', auth()->user()->USERID)->first();
        if($periode == null){
            return $this->setResponseError('Periode belum di set');
        }
        return $this->aktivaRepository->getSaldoAwal($perkiraan, $devisi, $periode);
    }

    public function setSaldoAwal(Request $request, $perkiraan, $devisi){
        $this->requestAjax($this->access, 'ISKOREKSI');
        $periode = $this->aktivaRepository->queryModel('dbperiode')->where('USERID', auth()->user()->USERID)->first();
        if($periode == null){
            return $this->setResponseError('Periode belum di set');
        }
        if($this->aktivaRepository->setSaldoAwal($request, $perkiraan, $devisi, $periode)){
            return $this->setResponseData(['datatable' => 'datatableMain']);
        }
    }

    public function getAktiva($perkiraan, $devisi){
        if(request()->ajax()){
            return $this->aktivaRepository->getAktiva($perkiraan, $devisi);
        }
        return abort(501, 'Data tidak ditemukan');
    }
}

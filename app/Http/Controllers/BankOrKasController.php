<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\BankOrKasInterface;
use App\Http\Services\CustomDataTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use stdClass;

class BankOrKasController extends Controller
{
    private $kasbankRepository;
    private $access;

    public function __construct(BankOrKasInterface $kasbankRepository)
    {
        $this->kasbankRepository = $kasbankRepository;
        $this->middleware(function ($request, $next) {
            $this->access = auth()->user()->getPermissionsName('02001');
            return $next($request);
        });
    }

    public function index()
    {
        if (request()->ajax()) {
            $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
            $hasOtorisasi2 = in_array('IsOtorisasi2', $this->access);
            $canKoreksi = in_array('ISKOREKSI', $this->access);
            $canCetak    = in_array('ISCETAK', $this->access);
            // dd($canCetak);
            $isExport = false;
            if (request()->length == 2147483647) {
                $isExport = true;
            }

            return CustomDataTable::init()->of($this->kasbankRepository->getAllBankOrKas())
                ->apply()
                ->mapData(function ($row) use ($hasOtorisasi1, $hasOtorisasi2, $isExport, $canCetak) {
                    $row->Tanggal = date('d/m/Y', strtotime($row->Tanggal));
                    $row->TotalD = number_format($row->TotalD, 2, ',', '.');
                    $row->TotalRp = number_format($row->TotalRp, 2, ',', '.');

                    if ($row->IsOtorisasi1 == 0 && $hasOtorisasi1) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Otorisasi" style="accent-color:#28a745!important;cursor:pointer"></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><i class="far fa-square text-success" title="Otorisasi 1 Belum dilakukan"></i></div>';
                    } else if ($row->IsOtorisasi1 == 0 && !$hasOtorisasi1) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-square text-success" title="Otorisasi 1 Belum dilakukan"></i></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><i class="far fa-square text-success" title="Otorisasi 1 Belum dilakukan"></i></div>';
                    } else if ($row->IsOtorisasi1 == 1 && $hasOtorisasi1 && $row->IsOtorisasi2 == 0 && $hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Sudah Ter Otorisasi" style="accent-color:#28a745!important;cursor:pointer" checked></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi2" title="Otorisasi" style="accent-color:#28a745!important;cursor:pointer"></div>';
                    } else if ($row->IsOtorisasi1 == 1 && !$hasOtorisasi1 && $row->IsOtorisasi2 == 0 && $hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Anda tidak bisa melakukan Otorisasi 1"></i></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi2" title="Otorisasi" style="accent-color:#28a745!important;cursor:pointer"></div>';
                    } else if ($row->IsOtorisasi1 == 1 && $hasOtorisasi1 && $row->IsOtorisasi2 == 0 && !$hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Sudah Ter Otorisasi" style="accent-color:#28a745!important;cursor:pointer" checked></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><i class="far fa-square text-success" title="Anda tidak bisa melakukan Otorisasi 2"></i></div>';
                    } else if ($row->IsOtorisasi1 == 1 && !$hasOtorisasi1 && $row->IsOtorisasi2 == 0 && !$hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Anda tidak bisa melakukan Otorisasi 1"></i></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><i class="far fa-square text-success" title="Anda tidak bisa melakukan Otorisasi 2"></i></div>';
                    } else if ($row->IsOtorisasi2 == 1 && $hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Otorisasi 2 s udah dilakukan"></i></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi2" title="Sudah Ter Otorisasi" style="accent-color:#28a745!important;cursor:pointer" checked></div>';
                    } else if ($row->IsOtorisasi2 == 1 && !$hasOtorisasi2) {
                        $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Otorisasi 2 sudah dilakukan"></i></div>';
                        $row->IsOtorisasi2Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Anda tidak bisa melakukan Otorisasi 2"></i></div>';
                    }

                    $row->canExport = $canCetak;
                    $row->table_expand = $isExport ? '' : view('components.accounting.kasbank.expand_table', ['NoBukti' => $row->NoBukti])->render();
                    if (!$isExport) {
                        $row->indikatorExpand = $this->kasbankRepository->getKasBankDetailByNoBukti($row->NoBukti)->count() > 0 ? true : false;
                    }
                    return $row;
                })
                ->addColumn('action', function ($data) use ($canKoreksi) {
                    $html = '';
                    if ($canKoreksi || $data->canExport) {
                        $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';

                        if ($data->canExport) {
                            $html .= "<button class='btn btn-primary btn-sm mr-1 download-pdf' data-bukti='{$data->NoBukti}'><i class='fa fa-file-pdf text-white mr-1'></i>PDF</button>";
                        }


                        if ($canKoreksi && ($data->IsOtorisasi2 == 0 && $data->IsOtorisasi1 == 0)) {
                            $url = route('accounting.bank-or-kas.detail-kasbank');
                            $html .= "<button class='btn btn-primary btn-sm mr-1 btnEditBukti' data-bukti='{$data->NoBukti}' data-url='{$url}'><i class='fa fa-eye text-white mr-1'></i>Detail</button>";
                        }
                        if (in_array('ISHAPUS', $this->access) && ($data->IsOtorisasi2 == 0 && $data->IsOtorisasi1 == 0)) {
                            $url = route('accounting.bank-or-kas.delete');
                            $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete kasbank' data-url='{$url}' data-id='{$data->NoBukti}' data-key='{$data->NoBukti}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                        }
                        $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    }
                    return $html;
                })
                ->done();
        }
        return view('accounting.bank-or-kas');
    }

    public function getKasBankByNoBukti($NoBukti)
    {
        return $this->kasbankRepository->getKasBankByNoBukti($NoBukti);
    }

    public function getKasBankDetailByNoBukti()
    {
        if (!request()->NoBukti) {
            return $this->setResponseError('No Bukti tidak boleh kosong');
        }
        $this->requestAjax($this->access, 'HASACCESS');
        $trans = $this->kasbankRepository->getKasBankByNoBukti(request()->NoBukti);
        $datatableData =  CustomDataTable::init()->of($this->kasbankRepository->getKasBankDetailByNoBukti(request()->NoBukti))
            ->apply()
            ->mapData(function ($row) {
                $row->Kurs = number_format($row->Kurs, 2, ',', '.');
                $row->Debet = number_format($row->Debet, 2, ',', '.');
                $row->DebetRp = number_format($row->DebetRp, 2, ',', '.');
                $row->KreditRp = number_format($row->KreditRp, 2, ',', '.');

                return $row;
            })
            ->addColumn('action', function ($data) use ($trans) {
                $html = '';
                if ($trans->IsOtorisasi2 == 0 && $trans->IsOtorisasi1 == 0) {
                    $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';
                    if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditKasBank btn--detail' data-bukti='{$data->NoBukti}' data-tanggal='{$data->Tanggal}' data-urut='{$data->Urut}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $this->access)) {
                        $url = route('accounting.bank-or-kas.delete-kasbank');
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$data->NoBukti}' data-urut='{$data->Urut}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                }
                return $html;
            })
            ->done();
        $datatableData += ['canAdd' => ($trans->IsOtorisasi1 == 0 && $trans->IsOtorisasi2 == 0)];
        return $datatableData;
    }

    public function setOtorisasi(Request $request)
    {
        if ($request->status == 0) {
            if (!in_array('IsBatal', $this->access)) {
                return $this->setResponseError('Anda tidak memiliki akses untuk membatalkan otorisasi');
            }
        }

        $this->requestAjax($this->access, $request->otoLevel);
        if ($this->kasbankRepository->setOtorisasi($request)) {
            return $this->setResponseSuccess('Berhasil Otorisasi');
        }

        return $this->setResponseError('Gagal melakukan otorisasi');
    }

    public function getNomorBukti()
    {
        $this->requestAjax($this->access, 'HASACCESS');
        return $this->kasbankRepository->getNomorBukti(request()->tipe);;
    }

    public function store(Request $request)
    {
        $this->requestAjax($this->access, 'ISTAMBAH');
        $this->kasbankRepository->store($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function update(Request $request)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        $this->kasbankRepository->update($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function delete(Request $request)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->kasbankRepository->delete($request->NoBukti)) {
            return $this->setResponseSuccess('Berhasil menghapus data');
        }
        return $this->setResponseError('Gagal menghapus data');
    }

    public function getDetailKasBankByNoBukti($NoBukti, $Tanggal, $Urut)
    {
        return $this->kasbankRepository->getDetailKasBankByNoBukti($NoBukti, $Tanggal, $Urut);
    }

    public function storeKasbank(Request $request)
    {
        $this->requestAjax($this->access, 'ISTAMBAH');
        $this->kasbankRepository->storeKasbank($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function updateKasBank(Request $request)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        $this->kasbankRepository->updateKasBank($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function deleteKasBank(Request $request)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->kasbankRepository->deleteKasBank($request->NoBukti, $request->Urut)) {
            return $this->setResponseSuccess('Berhasil menghapus data');
        }
        return $this->setResponseError('Gagal menghapus data');
    }

    public function downloadKasBank(Request $request)
    {
        $NoBukti = $request->bukti;
        $type = $request->type;

        if (!$NoBukti || !$type) {
            return $this->setResponseError('Gagal download file, parameter tidak lengkap');
        }

        if ($type === 'pdf') {
            return $this->downloadPDF($NoBukti);
        } else {
            return $this->setResponseError('Gagal download file, parameter tidak lengkap');
        }
    }

    private function downloadPDF($NoBukti)
    {
        $trans = $this->kasbankRepository->getKasBankByNoBukti($NoBukti);
        if (!$trans->NoBukti) {
            dd('haloo');
            return $this->setResponseError('Gagal download file, data tidak ditemukan');
        }
        if (request()->preview) {
            return view('layouts.pdf_layout', [
                'data' => (object)[
                    'trans' => $trans,
                    'detail' => $this->kasbankRepository->getKasBankDetailByNoBukti($NoBukti),
                ],
                'header' => 'components.accounting.kasbank.pdf-header',
                'body' => 'components.accounting.kasbank.pdf-body',
                'preview' => true,
                // 'footer' => 'components.accounting.kasbank.pdf-footer',
            ]);
        }
        $pdf = Pdf::loadView('layouts.pdf_layout', [
            'data' => (object)[
                'trans' => $trans,
                'detail' => $this->kasbankRepository->getKasBankDetailByNoBukti($NoBukti),
            ],
            'header' => 'components.accounting.kasbank.pdf-header',
            'body' => 'components.accounting.kasbank.pdf-body',
            // 'footer' => 'components.accounting.kasbank.pdf-footer',
        ]);
        return $pdf->stream('kasbank.pdf');
    }

    public function getDataHutang(Request $request)
    {
        $canKoreksi = in_array('ISKOREKSI', $this->access);
        $canHapus = in_array('ISHAPUS', $this->access);
        $NoBukti = $request->NoBukti;
        $Urut = $request->Urut;
        $Lawan = $request->Lawan;
        // dd($Lawan);
        if($Urut == null){
            $transaksi = $this->kasbankRepository->getKasBankDetailByNoBukti($NoBukti);
            $Urut = count($transaksi) + 1;
        }

        $data = $this->kasbankRepository->getDataHutang($request->kode, $Lawan);
        $tempData = $data;
        $saldoAwl = 0;
        $tempData = $tempData->groupBy('NoFaktur')->map(function($data) use($saldoAwl){
            $Debet = $data->sum('Debet');
            $Kredit = $data->sum('Kredit');
            $NoFaktur = $data->first()->NoFaktur;
            $saldoAwl = $data->where('TipeTrans', 'AWL')->first()->Saldo ?? 0;
            $sisa = $Debet - $Kredit;
            $newData = new stdClass();
            $newData->NoFaktur = $NoFaktur;
            $newData->Debet = $Debet;
            $newData->Kredit = $Kredit;
            $newData->Sisa = abs($sisa);
            $newData->SaldoAwal = $saldoAwl;
            // if($NoFaktur == '00208/LPB/PWT/042022'){
            //     dd($newData);
            // }
            return $newData;
        });
        
        // dd($tempData['00017/LPB/PRR/032014']->sum('Debet') - $tempData['00017/LPB/PRR/032014']->sum('Kredit'));
        $data = $data->map(function ($item) use ($tempData) {
            // if ($item->NoFaktur == '00054/INV/PWT/032022') {
            //     dd($item->NoFaktur, $tempData[$item->NoFaktur]->Sisa);
            // }
            if($item->TipeTrans != 'AWL' && $item->NoFaktur != null){
                $item->Saldo = $item->SaldoAwal;
            }
            $item->sisa = $tempData[$item->NoFaktur]->Sisa;

            return $item;
        });
        return CustomDataTable::init()->of($data)->mapData(function ($data) {
            $data->Tanggal = date('d/m/Y', strtotime($data->Tanggal));
            $data->JatuhTempo = date('d/m/Y', strtotime($data->JatuhTempo));

            $data->DebetRp = number_format($data->Debet, 2, ',', '.');
            $data->KreditRp = number_format($data->Kredit, 2, ',', '.');
            $data->SaldoRp = number_format($data->Saldo, 2, ',', '.');
            $data->KursRp = number_format($data->Kurs, 2, ',', '.');
            $data->JumlahSaldoRp = number_format($data->JumlahSaldo, 2, ',', '.');
            $data->DebetDRp = number_format($data->DebetD, 2, ',', '.');
            $data->KreditDRp = number_format($data->KreditD, 2, ',', '.');


            return $data;
        })
            ->addColumn('action', function ($data) use ($canKoreksi, $canHapus, $NoBukti, $Urut, $Lawan) {
                $html = '';
                if ($canHapus && $data->NoBukti == $NoBukti && $data->NoMsk == $Urut && $data->TipeTrans == 'L') {
                    $html .= '<button class="btn btn-sm btn-secondary btnHapusHutang" data-id="' . $data->NoMsk . '"><i class="fa fa-trash mr-2"></i>Hapus</button>';
                } else if ( $data->TipeTrans != 'L' && $data->sisa > 0 && $Lawan == 'HT') {
                    $html .= '<button class="btn btn-sm btn-primary btnBayarHutang" data-id="' . $data->NoMsk . '" data-nofaktur="' .  $data->NoFaktur . '"><i class="fa fa-money-bill mr-2"></i>Pelunasan</button>';
                } else if ( $data->TipeTrans != 'L' && $data->sisa > 0 && $Lawan == 'PT') {
                    $html .= '<button class="btn btn-sm btn-primary btnBayarHutang" data-id="' . $data->NoMsk . '" data-nofaktur="' .  $data->NoFaktur . '"><i class="fa fa-money-bill mr-2"></i>Penambahan</button>';
                } else {
                    $html .= "only-read";
                }

                return $html;
            })
            ->done();
    }

    public function pelunasanHutang(Request $request)
    {
        $this->requestAjax();
        if($this->kasbankRepository->pelunasanHutang($request)){
            return $this->setResponseSuccess('Data berhasil ditambah');
        }else{
            return $this->setResponseError('Data gagal ditambah');
        }
    }

    public function hapusPelunasan(Request $request)
    {
        $this->requestAjax();
        if($this->kasbankRepository->hapusPelunasan($request)){
            return $this->setResponseSuccess('Data berhasil dihapus');
        }else{
            return $this->setResponseError('Data gagal dihapus');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\MemorialInterface;
use App\Http\Services\CustomDataTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use stdClass;

class MemorialController extends Controller
{
    private $memorialRepository;
    private $access;

    public function __construct(MemorialInterface $memorialRepository)
    {
        $this->memorialRepository = $memorialRepository;
        $this->middleware(function ($request, $next) {
           // \Log::info('Middleware running for user: ' . auth()->user()->USERID);
            $this->access = auth()->user()->getPermissionsName('02002');
            return $next($request);
        });
    }

    public function index()
    {
        if (request()->ajax()) {
            try {
                // Define all variables first
                $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
                $hasOtorisasi2 = in_array('IsOtorisasi2', $this->access);
                $canKoreksi = in_array('ISKOREKSI', $this->access);
                $canCetak = in_array('ISCETAK', $this->access);
                $isExport = request()->length == 2147483647;

                // Get memorial data
                $memorialData = $this->memorialRepository->getAllMemorial();
                if (!$memorialData) {
                    return $this->setResponseError('Data tidak ditemukan');
                }

                return CustomDataTable::init()
                    ->of($memorialData)
                    ->apply()
                    ->mapData(function ($row) use ($hasOtorisasi1, $hasOtorisasi2, $isExport, $canCetak) {
                        // Format dates and numbers
                        $row->Tanggal = date('d/m/Y', strtotime($row->Tanggal));
                        $row->TotalD = number_format((float)$row->TotalD, 2, ',', '.');
                        $row->TotalRp = number_format((float)$row->TotalRp, 2, ',', '.');
                        $row->canExport = $canCetak;

                        // Handle otorisasi1 HTML (Memorial hanya menggunakan otorisasi1)
                        if ($row->IsOtorisasi1 == 0 && $hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Otorisasi" style="accent-color:#28a745!important;cursor:pointer"></div>';
                        } else if ($row->IsOtorisasi1 == 0 && !$hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-square text-success" title="Otorisasi 1 Belum dilakukan"></i></div>';
                        } else if ($row->IsOtorisasi1 == 1 && $hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Sudah Ter Otorisasi" style="accent-color:#28a745!important;cursor:pointer" checked></div>';
                        } else if ($row->IsOtorisasi1 == 1 && !$hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Anda tidak bisa melakukan Otorisasi 1"></i></div>';
                        }

                        // Memorial tidak menggunakan otorisasi2, jadi kosongkan atau hide
                        $row->IsOtorisasi2Html = '<div class="text-center">-</div>';
                        if (!$isExport) {
                            // Get detail data
                            $details = $this->memorialRepository->getMemorialDetailByNoBukti($row->NoBukti);
                            $row->indikatorExpand = !empty($details);
                            $row->table_expand = view('components.accounting.memorial.expand_table', [
                                'NoBukti' => $row->NoBukti
                            ])->render();
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

                            if ($canKoreksi && $data->IsOtorisasi1 == 0) {
                                $url = route('accounting.memorial.detail-memorial');
                                $html .= "<button class='btn btn-primary btn-sm mr-1 btnEditBukti' data-bukti='{$data->NoBukti}' data-url='{$url}'><i class='fa fa-eye text-white mr-1'></i>Detail</button>";
                            }

                            if (in_array('ISHAPUS', $this->access) && $data->IsOtorisasi1 == 0) {
                                $url = route('accounting.memorial.delete');
                                $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete memorial' data-url='{$url}' data-id='{$data->NoBukti}' data-key='{$data->NoBukti}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                            }

                            $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                        }
                        return $html;
                    })
                    ->done();

            } catch (\Exception $e) {
                \Log::error('Error in Memorial index: ' . $e->getMessage());
                return $this->setResponseError($e->getMessage());
            }
        }

        return view('accounting.memorial');
    }

    public function getMemorialByNoBukti($NoBukti)
    {
        return $this->memorialRepository->getMemorialByNoBukti($NoBukti);
    }

    public function store(Request $request)
    {
        $this->requestAjax($this->access, 'ISTAMBAH');
        $this->memorialRepository->store($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function storeMemorial(Request $request)
    {
        try {
            \Log::info('Memorial Detail Store Request:', $request->all());
            
            $this->requestAjax($this->access, 'ISTAMBAH');
            // Panggil method storeMemorial di repository, bukan store
            $result = $this->memorialRepository->storeMemorial($request);
            
            \Log::info('Memorial Detail Store Result:', ['result' => $result]);
            
            return $this->setResponseSuccess('Berhasil menyimpan data');
        } catch (\Exception $e) {
            \Log::error('Memorial Detail Store Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function updateMemorial(Request $request)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        $this->memorialRepository->updateMemorial($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function deleteMemorial(Request $request)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->memorialRepository->deleteMemorial($request->NoBukti, $request->Urut)) {
            return $this->setResponseSuccess('Berhasil menghapus data');
        }
        return $this->setResponseError('Gagal menghapus data');
    }

    public function update(Request $request)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        $this->memorialRepository->update($request);
        return $this->setResponseSuccess('Berhasil menyimpan data');
    }

    public function delete(Request $request)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        if ($this->memorialRepository->delete($request->NoBukti)) {
            return $this->setResponseSuccess('Berhasil menghapus data');
        }
        return $this->setResponseError('Gagal menghapus data');
    }

    public function setOtorisasi(Request $request)
    {
        if ($request->status == 0) {
            if (!in_array('IsBatal', $this->access)) {
                return $this->setResponseError('Anda tidak memiliki akses untuk membatalkan otorisasi');
            }
        }

        $this->requestAjax($this->access, $request->otoLevel);
        if ($this->memorialRepository->setOtorisasi($request)) {
            return $this->setResponseSuccess('Berhasil Otorisasi');
        }

        return $this->setResponseError('Gagal melakukan otorisasi');
    }

    public function downloadMemorial(Request $request)
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
        $trans = $this->memorialRepository->getMemorialByNoBukti($NoBukti);
        if (!$trans->NoBukti) {
            return $this->setResponseError('Gagal download file, data tidak ditemukan');
        }
        $pdf = Pdf::loadView('layouts.pdf_layout', [
            'data' => (object)[
                'trans' => $trans,
                'detail' => $this->memorialRepository->getMemorialDetailByNoBukti($NoBukti),
            ],
            'header' => 'components.accounting.memorial.pdf-header',
            'body' => 'components.accounting.memorial.pdf-body',
        ]);
        return $pdf->stream('memorial.pdf');
    }

        public function getNomorBukti()
    {
        $this->requestAjax($this->access, 'HASACCESS');
        return $this->memorialRepository->getNomorBukti(request()->tipe);;
    }

    public function getDetailMemorialByNoBukti($NoBukti, $Tanggal, $Urut)
    {
        return $this->memorialRepository->getDetailMemorialByNoBukti($NoBukti, $Tanggal, $Urut);
    }

     public function getMemorialDetailByNoBukti()
    {
    try {
        if (!request()->NoBukti) {
            return $this->setResponseError('No Bukti tidak boleh kosong');
        }

        $this->requestAjax($this->access, 'HASACCESS');
        
        // Get trans data and convert to object if it's a collection
        $trans = $this->memorialRepository->getMemorialByNoBukti(request()->NoBukti);
        if ($trans instanceof \Illuminate\Support\Collection) {
            $trans = $trans->first();
        }


        if (!$trans) {
            return $this->setResponseError('Data tidak ditemukan');
        }

        $datatableData = CustomDataTable::init()
            ->of($this->memorialRepository->getMemorialDetailByNoBukti(request()->NoBukti))
            ->apply()
            ->mapData(function ($row) {
                // Pastikan Keterangan tidak null
                $row->Keterangan = $row->Keterangan ?? '';
                
                // Format JumlahRp
                $row->JumlahRp = number_format(floatval($row->JumlahRp) ?? 0, 2, ',', '.');
                
                return $row;
            })
            ->addColumn('action', function ($data) use ($trans) {
                $html = '';
                if (($trans->IsOtorisasi1 ?? 0) == 0) {
                    $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                        <div class="notification-container close-button-container">';
                    if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditMemorial btn--detail' data-bukti='{$data->NoBukti}' data-tanggal='{$data->Tanggal}' data-urut='{$data->Urut}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                    }
                    if (in_array('ISHAPUS', $this->access)) {
                        $url = route('accounting.memorial.delete-memorial');
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$data->NoBukti}' data-urut='{$data->Urut}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                    }
                    $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                }
                return $html;
            })
            ->done();

        $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];
        
        return $datatableData;
    
    } catch (\Exception $e) {
        \Log::error('Memorial Detail Error:', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
        return $this->setResponseError($e->getMessage());
    }
    }
    
    // Hutang Piutang Methods
    public function getDataHutang(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'HASACCESS');
            
            $kode = $request->kode;
            $lawan = $request->Lawan;
            
            if (!$kode || !$lawan) {
                return $this->setResponseError('Parameter tidak lengkap');
            }
            
            $hutpiutData = $this->memorialRepository->getDataHutang($kode, $lawan);
            
            return CustomDataTable::init()
                ->of($hutpiutData)
                ->apply()
                ->mapData(function ($row) use ($request) {
                    // Format currency fields
                    $row->DebetRp = number_format((float)$row->Debet * (float)$row->Kurs, 2, ',', '.');
                    $row->KreditRp = number_format((float)$row->Kredit * (float)$row->Kurs, 2, ',', '.');
                    $row->KursRp = number_format((float)$row->Kurs, 2, ',', '.');
                    $row->DebetDRp = number_format((float)$row->DebetD, 2, ',', '.');
                    $row->KreditDRp = number_format((float)$row->KreditD, 2, ',', '.');
                    
                    // Calculate saldo
                    $saldo = (float)$row->Kredit - (float)$row->Debet;
                    $row->SaldoRp = number_format($saldo * (float)$row->Kurs, 2, ',', '.');
                    $row->JumlahSaldoRp = number_format($saldo * (float)$row->Kurs, 2, ',', '.');
                    
                    // Format dates
                    $row->Tanggal = date('d/m/Y', strtotime($row->Tanggal));
                    $row->JatuhTempo = $row->JatuhTempo ? date('d/m/Y', strtotime($row->JatuhTempo)) : '';
                    
                    // Add NOSO field (assuming NoInvoice as NOSO)
                    $row->NOSO = $row->NoInvoice ?? '';
                    
                    // Add action buttons
                    if ($row->TipeTrans == 'L') {
                        $row->action = '<button class="btn btn-danger btn-sm deleteHutang" data-all="false"><i class="fa fa-trash"></i></button>';
                    } else {
                        $row->action = '<button class="btn btn-primary btn-sm modalHutangPelunasan"><i class="fa fa-check"></i> Lunasi</button>';
                    }
                    
                    return $row;
                })
                ->done();
                
        } catch (\Exception $e) {
            \Log::error('Memorial getDataHutang Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function pelunasanHutang(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');
            
            if ($this->memorialRepository->pelunasanHutang($request)) {
                return $this->setResponseSuccess('Berhasil melakukan pelunasan');
            }
            
            return $this->setResponseError('Gagal melakukan pelunasan');
            
        } catch (\Exception $e) {
            \Log::error('Memorial pelunasanHutang Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function hapusPelunasan(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');
            
            if ($this->memorialRepository->hapusPelunasan($request)) {
                return $this->setResponseSuccess('Berhasil menghapus pelunasan');
            }
            
            return $this->setResponseError('Gagal menghapus pelunasan');
            
        } catch (\Exception $e) {
            \Log::error('Memorial hapusPelunasan Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }
}
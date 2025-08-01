<?php

namespace App\Http\Controllers;

use App\Http\Repository\SPKRepository;
use App\Http\Services\CustomDataTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SPKController extends Controller
{
    private $spkRepository;
    private $access;

    public function __construct(SPKRepository $spkRepository)
    {
        $this->spkRepository = $spkRepository;
        $this->middleware(function ($request, $next) {
            $this->access = auth()->user()->getPermissionsName('08103');
            return $next($request);
        });
    }

    public function index()
    {
        if (request()->ajax()) {
            try {
                $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
                $canKoreksi = in_array('ISKOREKSI', $this->access);
                $canCetak = in_array('ISCETAK', $this->access);
                $isExport = request()->length == 2147483647;

                $spkData = $this->spkRepository->getAllSpk();

                    if (!$spkData) {
                        return $this->setResponseError('Data tidak ditemukan');
                    }

                    return CustomDataTable::init()
                        ->of($spkData)
                        ->apply()
                    ->mapData(function ($row) use ($hasOtorisasi1, $isExport, $canCetak) {
                            $row->Tanggal = date('d/m/Y', strtotime($row->Tanggal));
                            $row->Qnt = number_format((float)$row->Qnt, 2, ',', '.');
                            $row->canExport = $canCetak;

                        // Otorisasi 1
                        if ($row->IsOtorisasi1 == 0 && $hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Otorisasi" style="accent-color:#28a745!important;cursor:pointer"></div>';
                        } else if ($row->IsOtorisasi1 == 0 && !$hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-square text-success" title="Otorisasi 1 Belum dilakukan"></i></div>';
                        } else if ($row->IsOtorisasi1 == 1 && $hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><input type="checkbox" name="IsOtorisasi1" title="Sudah Ter Otorisasi" style="accent-color:#28a745!important;cursor:pointer" checked></div>';
                        } else if ($row->IsOtorisasi1 == 1 && !$hasOtorisasi1) {
                            $row->IsOtorisasi1Html = '<div class="text-center"><i class="far fa-check-square text-success" title="Anda tidak bisa melakukan Otorisasi 1"></i></div>';
                        }

                            if (!$isExport) {
                            $row->indikatorExpand = true;
                            $row->detailUrl = route('produksi.spk.detail-Spk') . '?NoBukti=' . $row->NoBukti;
                            $row->table_expand = view('components.produksi.spk.expand_table', [
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
                $url = route('produksi.spk.detail-Spk');
                $html .= "<button class='btn btn-primary btn-sm mr-1 btnEditBukti' data-bukti='{$data->NoBukti}' data-url='{$url}'><i class='fa fa-eye text-white mr-1'></i>Detail</button>";
            }

                            if (in_array('ISHAPUS', $this->access) && $data->IsOtorisasi1 == 0) {
                $url = route('produksi.spk.delete');
                $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete spk' data-url='{$url}' data-id='{$data->NoBukti}' data-key='{$data->NoBukti}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
            }

            $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
        }
        return $html;
                    })
                    ->done();
            } catch (\Exception $e) {
                \Log::error('Error in SPK index: ' . $e->getMessage());
                return $this->setResponseError($e->getMessage());
            }
        }
        return view('produksi.spk.index');
    }

    public function getSpkDetailByNoBukti()
    {
        try {
            \Log::info('SPK Detail Method Called', [
                'NoBukti' => request()->NoBukti,
                'method' => request()->method(),
                'is_ajax' => request()->ajax(),
                'headers' => request()->headers->all(),
                'all_inputs' => request()->all()
            ]);

            if (!request()->NoBukti) {
                \Log::error('SPK Detail: No Bukti kosong');
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            // Skip AJAX check for DataTables requests - just check if it's POST
            if (!request()->isMethod('post')) {
                return $this->setResponseError('Method tidak diizinkan');
            }

            $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
            if ($trans instanceof \Illuminate\Support\Collection) {
                $trans = $trans->first();
            }

            if (!$trans) {
                return $this->setResponseError('Data tidak ditemukan');
            }

            // Debug: Log the structure of $trans
            \Log::info('SPK Trans Data:', [
                'type' => gettype($trans),
                'is_array' => is_array($trans),
                'is_object' => is_object($trans),
                'content' => $trans,
                'keys' => is_array($trans) ? array_keys($trans) : (is_object($trans) ? get_object_vars($trans) : 'not array or object')
            ]);

            $isExport = request()->get('isExport', false);

            $datatableData = CustomDataTable::init()
                ->of($this->spkRepository->getSpkDetailByNoBukti(request()->NoBukti))
                ->apply()
                ->mapData(function ($row) use ($trans, $isExport) {
                    $row->Qnt = number_format(floatval($row->Qnt) ?? 0, 2, ',', '.');
                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $noBukti = request()->NoBukti;
                    $isOtorisasi1 = is_array($trans) ? ($trans['IsOtorisasi1'] ?? 0) : ($trans->IsOtorisasi1 ?? 0);

                    // Jika sudah diotorisasi, return dash
                    if ($isOtorisasi1 == 1) {
                        return '-';
                    }

                    // Jika belum diotorisasi, tampilkan action buttons dengan container memorial style
                    $html = '<div style="width: 1px; max-width: 100%; margin: auto">';
                    $html .= '<div class="notification-container close-button-container">';

                    // Edit Button
                    if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkDetail' data-bukti='{$noBukti}' data-urut='{$data->Urut}' title='Edit SPK Detail'>";
                        $html .= "<i class='fa fa-edit text-white mr-1'></i>Edit";
                        $html .= "</button>";
                    }

                    // Delete Button
                    if (in_array('ISHAPUS', $this->access)) {
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnDeleteSpkDetail' data-bukti='{$noBukti}' data-urut='{$data->Urut}' title='Hapus SPK Detail'>";
                        $html .= "<i class='fa fa-trash mr-1'></i>Hapus";
                        $html .= "</button>";
                    }

                    $html .= '</div>';
                    $html .= '</div>';

                    return $html;
                })
                ->done();

            $isOtorisasi1 = is_array($trans) ? ($trans['IsOtorisasi1'] ?? 0) : ($trans->IsOtorisasi1 ?? 0);
            $datatableData += ['canAdd' => ($isOtorisasi1 == 0)];

            return $datatableData;

        } catch (\Exception $e) {
            \Log::error('SPK Detail Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            if ($this->spkRepository->deleteSpk($request->NoBukti)) {
                return $this->setResponseSuccess('Berhasil menghapus data SPK');
            }

            return $this->setResponseError('Gagal menghapus data SPK');

        } catch (\Exception $e) {
            \Log::error('Error in SPK delete: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');
            $result = $this->spkRepository->storeSpk($request);
            return $this->setResponseSuccess('Berhasil menyimpan data SPK');
        } catch (\Exception $e) {
            \Log::error('Error in SPK store: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function setOtorisasi(Request $request)
    {
        try {
        if ($request->status == 0) {
            if (!in_array('IsBatal', $this->access)) {
                return $this->setResponseError('Anda tidak memiliki akses untuk membatalkan otorisasi');
            }
        }

        $this->requestAjax($this->access, $request->otoLevel);

        if ($this->spkRepository->setOtorisasi($request)) {
                return $this->setResponseSuccess('Berhasil melakukan otorisasi');
        }

        return $this->setResponseError('Gagal melakukan otorisasi');

        } catch (\Exception $e) {
            \Log::error('Error in SPK setOtorisasi: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }    // Debug method untuk test endpoint
    public function testSpkDetail()
    {
        try {
            \Log::info('Test SPK Detail Method Called', [
                'NoBukti' => request()->NoBukti,
                'method' => request()->method(),
                'all_inputs' => request()->all()
            ]);

            if (!request()->NoBukti) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Bukti tidak boleh kosong'
                ]);
            }

            $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
            if ($trans instanceof \Illuminate\Support\Collection) {
                $trans = $trans->first();
            }

            if (!$trans) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $level1Data = $this->spkRepository->getSpkDetailByNoBukti(request()->NoBukti);

            return response()->json([
                'success' => true,
                'message' => 'Test berhasil',
                'data' => $level1Data,
                'trans' => $trans,
                'access' => $this->access
            ]);

        } catch (\Exception $e) {
            \Log::error('Test SPK Detail Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Debug method for level 2 data
    public function debugSpkDetailLevel2()
    {
        try {
            $noBukti = request()->get('NoBukti', 'SPK/24/000001');
            $urut = request()->get('Urut', 1);

            // Get level 1 data
            $level1Data = $this->spkRepository->getSpkDetailByNoBukti($noBukti);

            // Get level 2 data
            $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut);

            return response()->json([
                'success' => true,
                'message' => 'Debug data retrieved successfully',
                'data' => [
                    'noBukti' => $noBukti,
                    'urut' => $urut,
                    'level1_data' => $level1Data,
                    'level2_data' => $level2Data,
                    'level1_columns' => $level1Data ? array_keys((array)$level1Data->first()) : [],
                    'level2_columns' => $level2Data ? array_keys((array)$level2Data->first()) : [],
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function getSpkDetailLevel2ByNoBukti()
    {
        try {
            if (!request()->NoBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            if (!request()->Urut) {
                return $this->setResponseError('Urut tidak boleh kosong');
            }

        $this->requestAjax($this->access, 'HASACCESS');

            // Get trans data and convert to object if it's a collection
            $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
            if ($trans instanceof \Illuminate\Support\Collection) {
                $trans = $trans->first();
            }

            if (!$trans) {
                return $this->setResponseError('Data tidak ditemukan');
            }

            // Get level 2 detail data
            $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut(request()->NoBukti, request()->Urut);

            $datatableData = CustomDataTable::init()
                ->of($level2Data)
                ->apply()
                ->mapData(function ($row) {
                    // Format data untuk level 2
                    $row->QntJ = number_format(floatval($row->QntJ) ?? 0, 2, ',', '.');
                    $row->IsiJ = number_format(floatval($row->IsiJ) ?? 0, 2, ',', '.');
                    $row->TarifMesin = number_format(floatval($row->TarifMesin) ?? 0, 2, ',', '.');
                    $row->TarifTenaker = number_format(floatval($row->TarifTenaker) ?? 0, 2, ',', '.');
                    $row->JamTenaker = number_format(floatval($row->JamTenaker) ?? 0, 2, ',', '.');
                    $row->JmlTenaker = number_format(floatval($row->JmlTenaker) ?? 0, 2, ',', '.');
                    $row->QNTSPK = number_format(floatval($row->QNTSPK) ?? 0, 2, ',', '.');

                    // Format tanggal
                    if (isset($row->TANGGAL)) {
                        $row->TANGGAL = date('d/m/Y', strtotime($row->TANGGAL));
                    }

                    // Pastikan data parent tersedia
                    $row->NoBukti = request()->NoBukti;
                    $row->UrutParent = request()->Urut;
                    $row->indikatorExpand = false;

                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $isOtorisasi1 = ($trans->IsOtorisasi1 ?? 0);

                    // Jika sudah diotorisasi, return dash
                    if ($isOtorisasi1 == 1) {
                        return '-';
                    }

                    // Jika belum diotorisasi, tampilkan action buttons dengan container memorial style
                    $html = '<div style="width: 1px; max-width: 100%; margin: auto">';
                    $html .= '<div class="notification-container close-button-container">';

                    $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                    $urutParent = isset($data->UrutParent) ? $data->UrutParent : request()->Urut;
                    $urutChild = isset($data->NoUrut) ? $data->NoUrut : ($data->Urut ?? '');

                    // Edit Button
                    if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkLevel2' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' title='Edit Jadwal'>";
                        $html .= "<i class='fa fa-edit text-white mr-1'></i>Edit";
                        $html .= "</button>";
                    }

                    // Delete Button
                    if (in_array('ISHAPUS', $this->access)) {
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnDeleteSpkLevel2' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' title='Hapus Jadwal'>";
                        $html .= "<i class='fa fa-trash mr-1'></i>Hapus";
                        $html .= "</button>";
                    }

                    $html .= '</div>';
                    $html .= '</div>';

                    return $html;
                })
                ->done();

            $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];

            return $datatableData;

        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 2 Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function getSpkDetailLevel2AllByNoBukti()
    {
        try {
            if (!request()->NoBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            $this->requestAjax($this->access, 'HASACCESS');

            // Get trans data and convert to object if it's a collection
            $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
            if ($trans instanceof \Illuminate\Support\Collection) {
                $trans = $trans->first();
            }

            if (!$trans) {
                return $this->setResponseError('Data tidak ditemukan');
            }

            // Get all level 2 detail data for this NoBukti (semua jadwal produksi)
            $level2AllData = $this->spkRepository->getSpkDetailLevel2AllByNoBukti(request()->NoBukti);

            $datatableData = CustomDataTable::init()
                ->of($level2AllData)
                ->apply()
                ->mapData(function ($row) {
                    // Format data untuk level 2
                    $row->QntJ = number_format(floatval($row->QntJ) ?? 0, 2, ',', '.');
                    $row->IsiJ = number_format(floatval($row->IsiJ) ?? 0, 2, ',', '.');
                    $row->TarifMesin = number_format(floatval($row->TarifMesin) ?? 0, 2, ',', '.');
                    $row->TarifTenaker = number_format(floatval($row->TarifTenaker) ?? 0, 2, ',', '.');
                    $row->JamTenaker = number_format(floatval($row->JamTenaker) ?? 0, 2, ',', '.');
                    $row->JmlTenaker = number_format(floatval($row->JmlTenaker) ?? 0, 2, ',', '.');
                    $row->QNTSPK = number_format(floatval($row->QNTSPK) ?? 0, 2, ',', '.');

                    // Format tanggal
                    if (isset($row->TANGGAL)) {
                        $row->TANGGAL = date('d/m/Y', strtotime($row->TANGGAL));
                    }

                    // Pastikan data parent tersedia
                    $row->NoBukti = request()->NoBukti;
                    $row->indikatorExpand = false;

                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $isOtorisasi1 = ($trans->IsOtorisasi1 ?? 0);

                    // Jika sudah diotorisasi, return dash
                    if ($isOtorisasi1 == 1) {
                        return '-';
                    }

                    // Jika belum diotorisasi, tampilkan action buttons dengan container memorial style
                    $html = '<div style="width: 1px; max-width: 100%; margin: auto">';
                    $html .= '<div class="notification-container close-button-container">';

                    $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                    $urutParent = isset($data->UrutParent) ? $data->UrutParent : ($data->Urut ?? '');
                    $urutChild = isset($data->NoUrut) ? $data->NoUrut : ($data->Urut ?? '');

                    // Edit Button
                        if (in_array('ISKOREKSI', $this->access)) {
                        $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkLevel2' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' title='Edit Jadwal'>";
                        $html .= "<i class='fa fa-edit text-white mr-1'></i>Edit";
                        $html .= "</button>";
                        }

                    // Delete Button
                        if (in_array('ISHAPUS', $this->access)) {
                        $html .= "<button class='btn btn-danger btn-sm mr-1 btnDeleteSpkLevel2' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' title='Hapus Jadwal'>";
                        $html .= "<i class='fa fa-trash mr-1'></i>Hapus";
                        $html .= "</button>";
                    }

                    $html .= '</div>';
                    $html .= '</div>';

                    return $html;
                })
                ->done();

            $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];

            return $datatableData;

        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 2 All Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    // CRUD methods for SPK Detail (Level 1)
    public function createDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');

            $noBukti = $request->get('NoBukti');
            if (!$noBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            // Get SPK data to check authorization
            $spk = $this->spkRepository->getSpkByNoBukti($noBukti);
            if ($spk instanceof \Illuminate\Support\Collection) {
                $spk = $spk->first();
            }

            if (!$spk || $spk->IsOtorisasi1 == 1) {
                return $this->setResponseError('SPK sudah diotorisasi atau tidak ditemukan');
            }

            return view('produksi.spk.detail.create', compact('noBukti', 'spk'));

        } catch (\Exception $e) {
            \Log::error('Error in createDetail: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function storeDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');

            $result = $this->spkRepository->storeSpkDetail($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil menambah SPK Detail');
            }

            return $this->setResponseError('Gagal menambah SPK Detail');

        } catch (\Exception $e) {
            \Log::error('Error in storeDetail: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function editDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');

            $noBukti = $request->get('NoBukti');
            $urut = $request->get('Urut');

            if (!$noBukti || !$urut) {
                return $this->setResponseError('No Bukti dan Urut tidak boleh kosong');
            }

            // Get SPK data to check authorization
            $spk = $this->spkRepository->getSpkByNoBukti($noBukti);
            if ($spk instanceof \Illuminate\Support\Collection) {
                $spk = $spk->first();
            }

            if (!$spk || $spk->IsOtorisasi1 == 1) {
                return $this->setResponseError('SPK sudah diotorisasi atau tidak ditemukan');
            }

            // Get detail data
            $detail = $this->spkRepository->getSpkDetailByNoBuktiAndUrut($noBukti, $urut);
            if (!$detail) {
                return $this->setResponseError('Data detail tidak ditemukan');
            }

            return view('produksi.spk.detail.edit', compact('noBukti', 'urut', 'spk', 'detail'));

        } catch (\Exception $e) {
            \Log::error('Error in editDetail: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function updateDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');

            $result = $this->spkRepository->updateSpkDetail($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil mengubah SPK Detail');
            }

            return $this->setResponseError('Gagal mengubah SPK Detail');

        } catch (\Exception $e) {
            \Log::error('Error in updateDetail: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function deleteDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            $result = $this->spkRepository->deleteSpkDetail($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil menghapus SPK Detail');
            }

            return $this->setResponseError('Gagal menghapus SPK Detail');

        } catch (\Exception $e) {
            \Log::error('Error in deleteDetail: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    // CRUD methods for Jadwal Produksi (Level 2)
    public function createJadwal(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');

            $noBukti = $request->get('NoBukti');
            if (!$noBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            // Get SPK data to check authorization
            $spk = $this->spkRepository->getSpkByNoBukti($noBukti);
            if ($spk instanceof \Illuminate\Support\Collection) {
                $spk = $spk->first();
            }

            if (!$spk || $spk->IsOtorisasi1 == 1) {
                return $this->setResponseError('SPK sudah diotorisasi atau tidak ditemukan');
            }

            return view('produksi.spk.jadwal.create', compact('noBukti', 'spk'));

        } catch (\Exception $e) {
            \Log::error('Error in createJadwal: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function storeJadwal(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISTAMBAH');

            $result = $this->spkRepository->storeSpkJadwal($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil menambah Jadwal Produksi');
            }

            return $this->setResponseError('Gagal menambah Jadwal Produksi');

        } catch (\Exception $e) {
            \Log::error('Error in storeJadwal: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function editJadwal(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');

            $noBukti = $request->get('NoBukti');
            $noUrut = $request->get('NoUrut');

            if (!$noBukti || !$noUrut) {
                return $this->setResponseError('No Bukti dan No Urut tidak boleh kosong');
            }

            // Get SPK data to check authorization
            $spk = $this->spkRepository->getSpkByNoBukti($noBukti);
            if ($spk instanceof \Illuminate\Support\Collection) {
                $spk = $spk->first();
            }

            if (!$spk || $spk->IsOtorisasi1 == 1) {
                return $this->setResponseError('SPK sudah diotorisasi atau tidak ditemukan');
            }

            // Get jadwal data
            $jadwal = $this->spkRepository->getSpkJadwalByNoBuktiAndNoUrut($noBukti, $noUrut);
            if (!$jadwal) {
                return $this->setResponseError('Data jadwal tidak ditemukan');
            }

            return view('produksi.spk.jadwal.edit', compact('noBukti', 'noUrut', 'spk', 'jadwal'));

        } catch (\Exception $e) {
            \Log::error('Error in editJadwal: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function updateJadwal(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');

            $result = $this->spkRepository->updateSpkJadwal($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil mengubah Jadwal Produksi');
            }

            return $this->setResponseError('Gagal mengubah Jadwal Produksi');

        } catch (\Exception $e) {
            \Log::error('Error in updateJadwal: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    public function deleteJadwal(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            $result = $this->spkRepository->deleteSpkJadwal($request);
            if ($result) {
                return $this->setResponseSuccess('Berhasil menghapus Jadwal Produksi');
            }

            return $this->setResponseError('Gagal menghapus Jadwal Produksi');

        } catch (\Exception $e) {
            \Log::error('Error in deleteJadwal: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    /**
     * Get SPK statistics for dashboard
     */
    public function statistics()
    {
        try {
            if (!request()->isMethod('get')) {
                return $this->setResponseError('Method tidak diizinkan');
            }

            // Get basic SPK statistics
            $totalSpk = $this->spkRepository->getTotalSpk();
            $authorizedSpk = $this->spkRepository->getAuthorizedSpk();
            $pendingSpk = $this->spkRepository->getPendingSpk();

            $statistics = [
                'total' => $totalSpk,
                'authorized' => $authorizedSpk,
                'pending' => $pendingSpk
            ];

            return $this->setResponseSuccess('Berhasil mengambil statistik SPK', $statistics);

        } catch (\Exception $e) {
            \Log::error('Error in statistics: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }

    /**
     * Get Outstanding SO data for DataTable (memorial style)
     */
    public function outstandingSo()
    {
        try {
            if (request()->ajax()) {
                \Log::info('Outstanding SO AJAX request received');

                // Get Outstanding SO data from repository
                $outstandingData = $this->spkRepository->getOutstandingSo();

                if ($outstandingData->isEmpty()) {
                    \Log::info('No Outstanding SO data found');
                    return response()->json([
                        'draw' => request()->get('draw'),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => []
                    ]);
                }

                \Log::info('Outstanding SO data count: ' . $outstandingData->count());

                // Format data for DataTable
                $formattedData = $outstandingData->map(function ($row) {
                    return [
                        'NOBUKTI' => $row->NOBUKTI,
                        'URUT' => $row->URUT,
                        'KODEBRG' => $row->KODEBRG,
                        'NAMABRG' => $row->NAMABRG ?? '',
                        'QntSO' => number_format((float)$row->QntSO, 2, ',', '.'),
                        'QntSPK' => number_format((float)$row->QntSPK, 2, ',', '.'),
                        'Saldo' => number_format((float)$row->Saldo, 2, ',', '.'),
                        'SaldoRaw' => (float)$row->Saldo, // For color coding
                        'Satuan' => $row->Satuan ?? '',
                        'tglmulai' => $row->tglmulai ? date('d/m/Y', strtotime($row->tglmulai)) : '',
                        'tglkirim' => $row->tglkirim ? date('d/m/Y', strtotime($row->tglkirim)) : '',
                        'tglselesai' => $row->tglselesai ? date('d/m/Y', strtotime($row->tglselesai)) : ''
                    ];
                });

                return response()->json([
                    'draw' => request()->get('draw'),
                    'recordsTotal' => $outstandingData->count(),
                    'recordsFiltered' => $outstandingData->count(),
                    'data' => $formattedData->toArray()
                ]);

            }

            return $this->setResponseError('Permintaan tidak valid');

        } catch (\Exception $e) {
            \Log::error('Error in outstandingSo: ' . $e->getMessage());
            return response()->json([
                'draw' => request()->get('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Outstanding SO summary statistics
     */
    /*public function outstandingSoSummary()
    {
        try {
            if (!request()->isMethod('get')) {
                return $this->setResponseError('Method tidak diizinkan');
            }

            \Log::info('Outstanding SO Summary request received');

            // Get Outstanding SO summary from repository
            $summary = $this->spkRepository->getOutstandingSoSummary();

            \Log::info('Outstanding SO Summary data: ', $summary);

            return $this->setResponseData($summary, 'Berhasil mengambil ringkasan Outstanding SO');

        } catch (\Exception $e) {
            \Log::error('Error in outstandingSoSummary: ' . $e->getMessage());
            return $this->setResponseError($e->getMessage());
        }
    }*/
}

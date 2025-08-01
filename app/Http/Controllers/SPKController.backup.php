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

                // Debug: Log permissions
                \Log::info('SPK Permissions Debug', [
                    'access_array' => $this->access,
                    'hasOtorisasi1' => $hasOtorisasi1,
                    'canKoreksi' => $canKoreksi,
                    'canCetak' => $canCetak,
                    'user_id' => auth()->user()->USERID ?? 'not_authenticated'
                ]);

                $spkData = $this->spkRepository->getAllSpk();
                //Log::info('SPK Data:', ['data' => $spkData]);
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
                            $row->indikatorExpand = true; // Atur true/false sesuai kebutuhan
                            $row->detailUrl = route('produksi.spk.detail-Spk') . '?NoBukti=' . $row->NoBukti;
                                $row->table_expand = view('components.produksi.spk.expand_table', [
                                    'NoBukti' => $row->NoBukti
                                ])->render();
                            }

                            // Debug: Log values untuk troubleshooting
                        \Log::info('SPK Row Debug', [
                            'NoBukti' => $row->NoBukti,
                            'IsOtorisasi1' => $row->IsOtorisasi1,
                            'hasOtorisasi1' => $hasOtorisasi1,
                            'row_type' => gettype($row->IsOtorisasi1),
                            'raw_value' => var_export($row->IsOtorisasi1, true)
                        ]);

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
            if (!request()->NoBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            $this->requestAjax($this->access, 'HASACCESS');

            // Get trans data and convert to object if it's a collection

            $trans = $this->spkRepository->getSpkByNoBukti(request()->NoBukti);
            if ($trans instanceof \Illuminate\Support\Collection) {
                $trans = $trans->first();
            }            if (!$trans) {
                return $this->setResponseError('Data tidak ditemukan');
            }

            $datatableData = CustomDataTable::init()
                ->of($this->spkRepository->getSpkDetailByNoBukti(request()->NoBukti))
                ->apply()
                ->mapData(function ($row) {
                    // Ensure Urut field is preserved
                    $row->Urut = $row->Urut ?? '';
                    $row->Qnt = number_format(floatval($row->Qnt) ?? 0, 2, ',', '.');
                    $row->Isi = number_format(floatval($row->Isi) ?? 0, 2, ',', '.');
                    // Pastikan NoBukti ada untuk child DataTable
                    $row->NoBukti = request()->NoBukti;

                    // Menandai sebagai level 1 data
                    $row->level = 1;
                    $row->parent_urut = null;

                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $html = '';
                    if (($trans->IsOtorisasi1 ?? 0) == 0) {
                        $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                            <div class="notification-container close-button-container">';
                        if (in_array('ISKOREKSI', $this->access)) {
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urut = isset($data->Urut) ? $data->Urut : '';
                            $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpk btn--detail' data-bukti='{$noBukti}' data-urut='{$urut}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                        }
                        if (in_array('ISHAPUS', $this->access)) {
                            $url = route('produksi.spk.delete');
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urut = isset($data->Urut) ? $data->Urut : '';
                            $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$noBukti}' data-urut='{$urut}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                        }
                        $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    }
                    return $html;
                })
                ->done();            // Dapatkan data level 1 terlebih dahulu
            $level1Data = $datatableData['data'];
            $allData = [];

            // Loop melalui setiap row level 1 dan tambahkan data level 2 di bawahnya
            foreach ($level1Data as $level1Row) {
                // Tambahkan row level 1
                $allData[] = $level1Row;

                // Dapatkan data level 2 untuk row ini
                $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut(request()->NoBukti, $level1Row->Urut);

                \Log::info('SPK Level 2 Debug', [
                    'NoBukti' => request()->NoBukti,
                    'Level1_Urut' => $level1Row->Urut,
                    'Level2_Count' => $level2Data ? count($level2Data) : 0,
                    'Level2_Sample' => $level2Data && count($level2Data) > 0 ? $level2Data[0] : null
                ]);

                if ($level2Data && count($level2Data) > 0) {
                    foreach ($level2Data as $level2Row) {
                        // Format data level 2 - mapping field sesuai dengan query dari DBJADWALPRD
                        $level2Row->QntJ = number_format(floatval($level2Row->QntJ) ?? 0, 2, ',', '.');
                        $level2Row->IsiJ = number_format(floatval($level2Row->IsiJ) ?? 0, 2, ',', '.');
                        $level2Row->TarifMesin = number_format(floatval($level2Row->TarifMesin) ?? 0, 2, ',', '.');
                        $level2Row->TarifTenaker = number_format(floatval($level2Row->TarifTenaker) ?? 0, 2, ',', '.');
                        $level2Row->JamTenaker = number_format(floatval($level2Row->JamTenaker) ?? 0, 2, ',', '.');
                        $level2Row->JmlTenaker = number_format(floatval($level2Row->JmlTenaker) ?? 0, 2, ',', '.');
                        $level2Row->QNTSPK = number_format(floatval($level2Row->QNTSPK) ?? 0, 2, ',', '.');

                        // Format tanggal
                        if (isset($level2Row->TANGGAL)) {
                            $level2Row->TANGGAL = date('d/m/Y', strtotime($level2Row->TANGGAL));
                        }
                        if (isset($level2Row->TanggalBukti)) {
                            $level2Row->TanggalBukti = date('d/m/Y', strtotime($level2Row->TanggalBukti));
                        }
                        if (isset($level2Row->TglExpired)) {
                            $level2Row->TglExpired = date('d/m/Y', strtotime($level2Row->TglExpired));
                        }

                        // Mapping field untuk konsistensi dengan DataTable level 1
                        // Level 2 tidak memiliki KodeBrg/NamaBrg, jadi kosongkan
                        $level2Row->KodeBrg = $level2Row->BrgJ ?? '';
                        $level2Row->NamaBrg = $level2Row->ket ?? '';
                        $level2Row->Qnt = $level2Row->QNTSPK ?? '0,00';
                        $level2Row->Satuan = $level2Row->SatJ ?? '';
                        $level2Row->Isi = $level2Row->IsiJ ?? '0,00';
                        $level2Row->Keterangan = $level2Row->Keterangan ?? '';

                        // Field spesifik level 2 dari query DBJADWALPRD
                        // Field ini sudah ada langsung dari query: KodePrs, KODEMSN, TANGGAL, JAMAWAL, JAMAKHIR, dll.

                        // Menandai sebagai level 2 data
                        $level2Row->level = 2;
                        $level2Row->parent_urut = $level1Row->Urut;
                        $level2Row->NoBukti = request()->NoBukti;

                        // Action button untuk level 2
                        if (($trans->IsOtorisasi1 ?? 0) == 0) {
                            $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                                <div class="notification-container close-button-container">';
                            if (in_array('ISKOREKSI', $this->access)) {
                                $urutChild = $level2Row->NoUrut ?? $level2Row->Urut ?? '';
                                $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkLevel2 btn--detail' data-bukti='{$level2Row->NoBukti}' data-urut-parent='{$level1Row->Urut}' data-urut-child='{$urutChild}'><i class='fa fa-pen mr-1'></i>Edit L2</button>";
                            }
                            if (in_array('ISHAPUS', $this->access)) {
                                $url = route('produksi.spk.delete-level2');
                                $urutChild = $level2Row->NoUrut ?? $level2Row->Urut ?? '';
                                $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$level2Row->NoBukti}' data-urut-parent='{$level1Row->Urut}' data-urut-child='{$urutChild}'><i class='fa fa-trash mr-1'></i>Hapus L2</button>";
                            }
                            $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                            $level2Row->action = $html;
                        } else {
                            $level2Row->action = '';
                        }

                        // Tambahkan row level 2
                        $allData[] = $level2Row;
                    }
                }
            }

            // Update datatableData dengan gabungan data level 1 dan 2
            $datatableData['data'] = $allData;
            $datatableData['recordsTotal'] = count($allData);
            $datatableData['recordsFiltered'] = count($allData);

            $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];

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

            // Get level 2 detail data (contoh: material/komponen yang digunakan)
            $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut(request()->NoBukti, request()->Urut);            $datatableData = CustomDataTable::init()
                ->of($level2Data)
                ->apply()
                ->mapData(function ($row) {
                    // Format data untuk level 2 - sesuai dengan query baru dari DBJADWALPRD
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
                    if (isset($row->TanggalBukti)) {
                        $row->TanggalBukti = date('d/m/Y', strtotime($row->TanggalBukti));
                    }
                    if (isset($row->TglExpired)) {
                        $row->TglExpired = date('d/m/Y', strtotime($row->TglExpired));
                    }

                    // Pastikan data parent tersedia
                    $row->NoBukti = request()->NoBukti;
                    $row->UrutParent = request()->Urut;

                    // Gunakan NoUrut sebagai UrutChild untuk expand level 3
                    $urutChild = $row->NoUrut ?? $row->Urut ?? '';

                    // Add expand capability for level 3 (untuk saat ini disable dulu karena belum ada data level 3 real)
                    $row->indikatorExpand = false; // Set false karena menggunakan data real dari DBJADWALPRD

                    // Jika ingin enable expand level 3, uncomment baris di bawah:
                    // $hasLevel3Data = $this->spkRepository->hasSpkDetailLevel3($row->NoBukti, $row->UrutParent, $urutChild);
                    // $row->indikatorExpand = $hasLevel3Data;
                    // if ($hasLevel3Data) {
                    //     $row->table_expand = view('components.produksi.spk.expand_table_level3', [
                    //         'NoBukti' => $row->NoBukti,
                    //         'UrutParent' => $row->UrutParent,
                    //         'UrutChild' => $urutChild
                    //     ])->render();
                    // }

                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $html = '';
                    if (($trans->IsOtorisasi1 ?? 0) == 0) {
                        $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                            <div class="notification-container close-button-container">';                        if (in_array('ISKOREKSI', $this->access)) {
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urutParent = isset($data->UrutParent) ? $data->UrutParent : request()->Urut;
                            $urutChild = isset($data->NoUrut) ? $data->NoUrut : ($data->Urut ?? '');
                            $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkLevel2 btn--detail' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                        }
                        if (in_array('ISHAPUS', $this->access)) {
                            $url = route('produksi.spk.delete-level2');
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urutParent = isset($data->UrutParent) ? $data->UrutParent : request()->Urut;
                            $urutChild = isset($data->NoUrut) ? $data->NoUrut : ($data->Urut ?? '');
                            $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                        }
                        $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    }
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
            return $this->setResponseError($e->getMessage());        }
    }

    public function getSpkDetailLevel3ByNoBukti()
    {
        try {
            if (!request()->NoBukti) {
                return $this->setResponseError('No Bukti tidak boleh kosong');
            }

            if (!request()->UrutParent) {
                return $this->setResponseError('Urut Parent tidak boleh kosong');
            }

            if (!request()->UrutChild) {
                return $this->setResponseError('Urut Child tidak boleh kosong');
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

            // Get level 3 detail data
            $level3Data = $this->spkRepository->getSpkDetailLevel3ByNoBuktiAndUrut(
                request()->NoBukti,
                request()->UrutParent,
                request()->UrutChild
            );

            $datatableData = CustomDataTable::init()
                ->of($level3Data)
                ->apply()
                ->mapData(function ($row) {
                    // Format data untuk level 3
                    $row->Qnt = number_format(floatval($row->Qnt) ?? 0, 2, ',', '.');
                    $row->Harga = number_format(floatval($row->Harga) ?? 0, 2, ',', '.');
                    $row->Total = number_format(floatval($row->Total) ?? 0, 2, ',', '.');

                    // Pastikan data parent tersedia
                    $row->NoBukti = request()->NoBukti;
                    $row->UrutParent = request()->UrutParent;
                    $row->UrutChild = request()->UrutChild;

                    return $row;
                })
                ->addColumn('action', function ($data) use ($trans) {
                    $html = '';
                    if (($trans->IsOtorisasi1 ?? 0) == 0) {
                        $html = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                            <div class="notification-container close-button-container">';
                        if (in_array('ISKOREKSI', $this->access)) {
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urutParent = isset($data->UrutParent) ? $data->UrutParent : request()->UrutParent;
                            $urutChild = isset($data->UrutChild) ? $data->UrutChild : request()->UrutChild;
                            $urutGrandChild = isset($data->UrutGrandChild) ? $data->UrutGrandChild : '';
                            $html .= "<button class='btn btn-warning btn-sm mr-1 btnEditSpkLevel3 btn--detail' data-bukti='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' data-urut-grandchild='{$urutGrandChild}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                        }
                        if (in_array('ISHAPUS', $this->access)) {
                            $url = route('produksi.spk.delete-level3');
                            $noBukti = isset($data->NoBukti) ? $data->NoBukti : request()->NoBukti;
                            $urutParent = isset($data->UrutParent) ? $data->UrutParent : request()->UrutParent;
                            $urutChild = isset($data->UrutChild) ? $data->UrutChild : request()->UrutChild;
                            $urutGrandChild = isset($data->UrutGrandChild) ? $data->UrutGrandChild : '';
                            $html .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete btn--detail' data-url='{$url}' data-id='{$noBukti}' data-urut-parent='{$urutParent}' data-urut-child='{$urutChild}' data-urut-grandchild='{$urutGrandChild}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                        }
                        $html .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                    }
                    return $html;
                })
                ->done();

            $datatableData += ['canAdd' => (($trans->IsOtorisasi1 ?? 0) == 0)];

            return $datatableData;

        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 3 Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    // Debug method untuk melihat data mentah
    public function debugSpkDetail()
    {
        try {
            $noBukti = request()->get('NoBukti', '00031/SPK/PWT/022022');
            $detailData = $this->spkRepository->getSpkDetailByNoBukti($noBukti);

            if ($detailData && count($detailData) > 0) {
                $firstRow = (array)$detailData[0];
                return response()->json([
                    'success' => true,
                    'count' => count($detailData),
                    'keys' => array_keys($firstRow),
                    'first_row' => $firstRow,
                    'all_data' => $detailData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found',
                    'count' => 0
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Test method untuk debug data structure
    public function testSpkDetail()
    {
        try {
            $noBukti = request()->get('NoBukti', '00031/SPK/PWT/022022');
            $detailData = $this->spkRepository->getSpkDetailByNoBukti($noBukti);

            if ($detailData && count($detailData) > 0) {
                $firstRow = (array)$detailData[0];
                return response()->json([
                    'success' => true,
                    'count' => count($detailData),
                    'keys' => array_keys($firstRow),
                    'first_row' => $firstRow,
                    'all_data' => $detailData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found',
                    'count' => 0
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // TODO: Add SPK methods here
    public function getRppData()
    {
        Log::info('getRppData method called', [
            'request_all' => request()->all(),
            'ajax' => request()->ajax(),
            'method' => request()->method()
        ]);

        try {
            if (request()->ajax()) {
                $hasOtorisasi1 = in_array('IsOtorisasi1', $this->access);
                $canKoreksi = in_array('ISKOREKSI', $this->access);
                $canCetak = in_array('ISCETAK', $this->access);
                $isExport = request()->length == 2147483647;

                // Debug: Log permissions
                \Log::info('SPK Permissions Debug', [
                    'access_array' => $this->access,
                    'hasOtorisasi1' => $hasOtorisasi1,
                    'canKoreksi' => $canKoreksi,
                    'canCetak' => $canCetak,
                    'user_id' => auth()->user()->USERID ?? 'not_authenticated'
                ]);

                $spkData = $this->spkRepository->getAllSpk();
                Log::info('getRppData SPK Data:', ['count' => count($spkData), 'sample' => array_slice($spkData, 0, 3)]);

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

                        // Debug: Log values untuk troubleshooting
                        \Log::info('SPK Row Debug', [
                            'NoBukti' => $row->NoBukti,
                            'IsOtorisasi1' => $row->IsOtorisasi1,
                            'hasOtorisasi1' => $hasOtorisasi1,
                            'row_type' => gettype($row->IsOtorisasi1),
                            'raw_value' => var_export($row->IsOtorisasi1, true)
                        ]);

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
            } else {
                Log::error('getRppData called but not AJAX request');
                return $this->setResponseError('Invalid request');
            }
        } catch (\Exception $e) {
            \Log::error('Error in getRppData: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
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
            \Log::error('Error in SPK delete: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function destroy($noBukti)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            if ($this->spkRepository->deleteSpk($noBukti)) {
                return $this->setResponseSuccess('Berhasil menghapus data SPK');
            }

            return $this->setResponseError('Gagal menghapus data SPK');

        } catch (\Exception $e) {
            \Log::error('Error in SPK destroy: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function deleteSpkDetail(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            if ($this->spkRepository->deleteSpkDetail($request->id, $request->urut)) {
                return $this->setResponseSuccess('Berhasil menghapus detail SPK');
            }

            return $this->setResponseError('Gagal menghapus detail SPK');

        } catch (\Exception $e) {
            \Log::error('Error in SPK deleteSpkDetail: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function deleteSpkDetailLevel2(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            if ($this->spkRepository->deleteSpkDetailLevel2($request->id, $request->urut_parent, $request->urut_child)) {
                return $this->setResponseSuccess('Berhasil menghapus detail SPK level 2');
            }

            return $this->setResponseError('Gagal menghapus detail SPK level 2');

        } catch (\Exception $e) {
            \Log::error('Error in SPK deleteSpkDetailLevel2: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
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
            \Log::error('Error in SPK store: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
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
            \Log::error('Error in SPK setOtorisasi: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function storeSpkDetailLevel2(Request $request)
    {
        try {
            \Log::info('SPK Detail Level 2 Store Request:', $request->all());

            $this->requestAjax($this->access, 'ISTAMBAH');

            $result = $this->spkRepository->storeSpkDetailLevel2($request);

            \Log::info('SPK Detail Level 2 Store Result:', ['result' => $result]);

            return $this->setResponseSuccess('Berhasil menyimpan data level 2');
        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 2 Store Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());        }
    }

    public function updateSpkDetailLevel2(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');
            $this->spkRepository->updateSpkDetailLevel2($request);
            return $this->setResponseSuccess('Berhasil mengupdate data level 2');
        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 2 Update Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    // CRUD Methods for SPK Detail Level 3
    public function deleteSpkDetailLevel3(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISHAPUS');

            if ($this->spkRepository->deleteSpkDetailLevel3(
                $request->id,
                $request->urut_parent,
                $request->urut_child,
                $request->urut_grandchild
            )) {
                return $this->setResponseSuccess('Berhasil menghapus detail SPK level 3');
            }

            return $this->setResponseError('Gagal menghapus detail SPK level 3');

        } catch (\Exception $e) {
            \Log::error('Error in SPK deleteSpkDetailLevel3: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function storeSpkDetailLevel3(Request $request)
    {
        try {
            \Log::info('SPK Detail Level 3 Store Request:', $request->all());

            $this->requestAjax($this->access, 'ISTAMBAH');

            $result = $this->spkRepository->storeSpkDetailLevel3($request);

            \Log::info('SPK Detail Level 3 Store Result:', ['result' => $result]);

            return $this->setResponseSuccess('Berhasil menyimpan data level 3');
        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 3 Store Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    public function updateSpkDetailLevel3(Request $request)
    {
        try {
            $this->requestAjax($this->access, 'ISKOREKSI');
            $this->spkRepository->updateSpkDetailLevel3($request);
            return $this->setResponseSuccess('Berhasil mengupdate data level 3');
        } catch (\Exception $e) {
            \Log::error('SPK Detail Level 3 Update Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->setResponseError($e->getMessage());
        }
    }

    // Debug method untuk melihat data level 2
    public function debugSpkDetailLevel2()
    {
        try {
            $noBukti = request()->get('NoBukti', '00031/SPK/PWT/022022');
            $urut = request()->get('Urut', 1);

            \Log::info('Debug SPK Level 2', [
                'NoBukti' => $noBukti,
                'Urut' => $urut
            ]);

            // Test level 1 data
            $level1Data = $this->spkRepository->getSpkDetailByNoBukti($noBukti);

            // Test level 2 data
            $level2Data = $this->spkRepository->getSpkDetailLevel2ByNoBuktiAndUrut($noBukti, $urut);

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'NoBukti' => $noBukti,
                    'Urut' => $urut,
                    'level1_count' => count($level1Data),
                    'level2_count' => count($level2Data),
                    'level1_sample' => $level1Data ? array_slice($level1Data, 0, 1) : [],
                    'level2_sample' => $level2Data ? array_slice($level2Data, 0, 1) : [],
                    'level1_keys' => $level1Data && count($level1Data) > 0 ? array_keys((array)$level1Data[0]) : [],
                    'level2_keys' => $level2Data && count($level2Data) > 0 ? array_keys((array)$level2Data[0]) : []
                ]
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

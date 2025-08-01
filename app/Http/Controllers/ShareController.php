<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\BaseInterface;
use App\Http\Repository\Task\GlobalInterface;
use App\Http\Services\CustomDataTable;
use App\Models\DBCUSTSUPP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShareController extends Controller
{
    private $baseRepository;
    private $globalRepository;

    public function __construct(BaseInterface $baseRepository, GlobalInterface $globalRepository)
    {
        $this->baseRepository = $baseRepository;
        $this->globalRepository = $globalRepository;
    }

    public function getUserAccess(Request $request)
    {
        dd('cuy');
    }

    public function getKaryawanSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbkaryawan')->when(request()->q !== NULL, function ($q) {
                return $q->orwhere('Nama', 'like', '%' . request()->q . '%')
                    ->orwhere('NIK', 'like', '%' . request()->q . '%');
            })->orderby('Nama', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KeyNIK,
                    'text' => "$data->NIK - $data->Nama",
                    'Description' => $data->Nama,
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getDepartemenSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbdepartemen')->when(request()->q !== NULL, function ($q) {
                return $q->where('NMDEP', 'like', '%' . request()->q . '%')
                    ->orwhere('KDDEP', 'like', '%' . request()->q . '%');
            })->orderby('NMDEP', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KDDEP,
                    'text' => "$data->NMDEP",
                    'Description' => $data->NMDEP,
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getJabatanSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbjabatan')->when(request()->q !== NULL, function ($q) {
                return $q->where('NamaJab', 'like', '%' . request()->q . '%')
                    ->orwhere('KODEJAB', 'like', '%' . request()->q . '%');
            })->orderby('NamaJab', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KODEJAB,
                    'text' => "$data->NamaJab",
                    'Description' => $data->NamaJab,
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getValasSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbvalas')->when(request()->q !== NULL, function ($q) {
                return $q->where('NAMAVLS', 'like', '%' . request()->q . '%')
                    ->orwhere('KODEVLS', 'like', '%' . request()->q . '%');
            })->orderby('NAMAVLS', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KODEVLS,
                    'text' => "($data->Simbol) $data->NAMAVLS - " . number_format($data->KURS, 2),
                    'Kurs' => number_format($data->KURS, 2),
                    'Description' => number_format($data->KURS, 2),
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getArusKasSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbaruskas')->when(request()->q !== NULL, function ($q) {
                return $q->where('NamaAK', 'like', '%' . request()->q . '%')
                    ->orwhere('KodeAK', 'like', '%' . request()->q . '%');
            })->orderby('NamaAK', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KodeAK,
                    'text' => "$data->NamaAK",
                    'Description' => $data->NamaAK,
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getArusKasDetSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->model('dbaruskasdet')->when(request()->q !== NULL, function ($q) {
                return $q->where('NamaSubAK', 'like', '%' . request()->q . '%')
                    ->orwhere('KodeSubAK', 'like', '%' . request()->q . '%');
            })->orderby('NamaSubAK', 'ASC')->getAll()->map(function ($data) {
                return [
                    'id' => $data->KodeSubAK,
                    'text' => "$data->NamaSubAK",
                    'Description' => $data->NamaSubAK,
                ];
            });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getPeriode()
    {
        $periode = $this->baseRepository->model('dbperiode')->where('USERID', auth()->user()->USERID)->firstOrNew();

        return response()->json($periode);
    }

    public function setPeriode(Request $request)
    {
        if ($this->baseRepository->queryModel('dbperiode')->where('USERID', auth()->user()->USERID)->update(['BULAN' => $request->BULAN, 'TAHUN' => $request->TAHUN])) {
            return $this->setResponseSuccess();
        }
        return $this->setResponseError();
    }

    public function getGroupAktivaSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->queryModel('dbposthutpiut')
                ->select('dbperkiraan.Perkiraan', 'dbposthutpiut.Akumulasi', 'by.Keterangan as KeteranganBiaya', 'dbposthutpiut.Biaya1', 'dbposthutpiut.Persen', 'dbposthutpiut.PersenBiaya1', 'dbperkiraan.Keterangan')
                ->join('dbperkiraan', 'dbposthutpiut.perkiraan', '=', 'dbperkiraan.perkiraan')
                ->join('dbperkiraan as by', 'dbposthutpiut.Akumulasi', '=', 'by.perkiraan')
                ->when(request()->q !== NULL, function ($q) {
                    return $q->where('dbperkiraan.Keterangan', 'like', '%' . request()->q . '%')
                        ->orwhere('dbperkiraan.Perkiraan', 'like', '%' . request()->q . '%');
                })
                ->where('dbposthutpiut.Kode', 'AKV')
                ->get()->map(function ($data) {
                    return [
                        'id' => $data->Perkiraan,
                        'text' => "$data->Perkiraan - $data->Keterangan",
                        'Akumulasi' => $data->Akumulasi,
                        'Persen' => $data->Persen,
                        'Biaya1' => $data->Biaya1,
                        'KeteranganBiaya' => $data->KeteranganBiaya,
                        'PersenBiaya1' => $data->PersenBiaya1,
                        'Keterangan' => $this->baseRepository->queryModel('dbperkiraan')->where('Perkiraan', $data->Akumulasi)->first()->Keterangan,
                        'Description' => $data->Keterangan,
                    ];
                });
        }
        return $this->setResponseError('Halaman tidak ditemukan', 404);
    }

    public function getDevisiSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->queryModel('dbdevisi')
                ->select('Devisi', 'NamaDevisi')
                ->when(request()->q !== NULL, function ($q) {
                    return $q->where('NamaDevisi', 'like', '%' . request()->q . '%')
                        ->orwhere('Devisi', 'like', '%' . request()->q . '%');
                })->orderby('NamaDevisi', 'ASC')->get()->map(function ($data) {
                    return [
                        'id' => $data->Devisi,
                        'text' => "$data->NamaDevisi",
                        'Description' => $data->NamaDevisi,
                    ];
                });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getAkumulasiPenyusutanSelect()
    {
        if (request()->ajax()) {
            // select a.*,b.Keterangan from dbposthutpiut a,dbperkiraan b 
            // where a.perkiraan=b.perkiraan and a.perkiraan=:0
            return $this->baseRepository->queryModel('dbposthutpiut')
                ->select('dbposthutpiut.*', 'dbperkiraan.Keterangan')
                ->join('dbperkiraan', 'dbposthutpiut.akumulasi', '=', 'dbperkiraan.perkiraan')
                ->when(request()->q !== NULL, function ($q) {
                    return $q->where('dbperkiraan.Keterangan', 'like', '%' . request()->q . '%')
                        ->orwhere('dbperkiraan.Perkiraan', 'like', '%' . request()->q . '%');
                })
                ->where('dbposthutpiut.Kode', 'AKV')
                ->get()->map(function ($data) {
                    return [
                        'id' => $data->Akumulasi,
                        'text' => "$data->Akumulasi - $data->Keterangan",
                        'Description' => $data->Keterangan,
                    ];
                });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getBiayaSelect()
    {
        if (request()->ajax()) {
            return $this->baseRepository->queryModel('dbperkiraan')->from('dbperkiraan as by')
                ->when(request()->q !== NULL, function ($q) {
                    return $q->where(function ($qr) {
                        $qr->where('by.Keterangan', 'like', '%' . request()->q . '%')
                            ->orwhere('by.Perkiraan', 'like', '%' . request()->q . '%');
                    });
                })
                ->when(request()->without, function ($q) {
                    return $q->where('by.Perkiraan', '!=', request()->without);
                })->when(request()->posthutpiut != null, function ($q) {
                    return $q->leftjoin('dbposthutpiut as pht', 'pht.Perkiraan', '=', 'by.Perkiraan')
                        // ->where('pht.Kode', request()->posthutpiut)
                        ->select('by.Keterangan', 'by.Perkiraan', 'pht.Kode');
                })
                ->when(request()->posthutpiut == null, function ($q) {
                    return $q->select('by.Keterangan', 'by.Perkiraan');
                })
                ->when(request()->perkiraan != null, function ($q) {
                    return $q->where('by.Perkiraan', request()->perkiraan);
                })
                ->where('by.Tipe', 1)
                ->get()->map(function ($data) {
                    return [
                        'id' => $data->Perkiraan,
                        'text' => "$data->Perkiraan - $data->Keterangan",
                        'Kode' => $data->Kode,
                        'Description' => $data->Keterangan,
                    ];
                });
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

    public function getKelompokKasSelect()
    {
        $this->requestAjax();
        return $this->baseRepository->queryModel('dbperkiraan')
            ->select('Perkiraan', 'Keterangan')
            ->when(request()->q !== NULL, function ($q) {
                return $q->where('Keterangan', 'like', '%' . request()->q . '%')
                    ->orwhere('Perkiraan', 'like', '%' . request()->q . '%');
            })
            ->whereDoesntHave('kelompok_kas')
            ->where('Tipe', 1)
            ->get()->map(function ($data) {
                return [
                    'id' => $data->Perkiraan,
                    'text' => "$data->Perkiraan - $data->Keterangan",
                    'Description' => $data->Keterangan,
                ];
            });
    }

    public function getKelompokKasOrBankSelect()
    {
        $this->requestAjax();
        return $this->globalRepository->getKelompokKasOrBank(request()->kode)
            ->map(function ($data) {
                return [
                    'id' => $data->Perkiraan,
                    'text' => "$data->Perkiraan - $data->Keterangan",
                    'Description' => $data->Keterangan,
                ];
            });
    }

    public function getNomorSPKSelect()
    {
        $this->requestAjax();
        return $this->globalRepository->getNomorSPK()->map(function ($data) {
            return [
                'id' => $data->NoSPK,
                'text' => "$data->NoSPK",
                'Description' => $data->NoSPK,
            ];
        });
    }

    public function getCustomerHutang()
    {
        $this->requestAjax();
        return CustomDataTable::init()->of($this->globalRepository->getCustomerHutang(request()->JENIS ?? NULL))
            ->apply()
            ->addColumn('action', function ($data) {
                return "<button class='btn btn-sm btn-primary btnPilihCustomer'><i class='fa fa-check'></i> Pilih</button>";
            })
            ->done();
    }
    
    /**
     * Get chart of accounts (Perkiraan) data for select2 dropdown
     * Returns filtered perkiraan data based on search criteria and parameters
     * 
     * @return \Illuminate\Http\JsonResponse|void JSON response for AJAX requests, 404 for non-AJAX
     */
    public function getPerkiraanSelect()
    {
        try {
            if (request()->ajax()) {
                $data = $this->baseRepository->queryModel('dbperkiraan')
                    ->select('Perkiraan', 'Keterangan')
                    ->when(request()->q !== NULL, function ($q) {
                        return $q->where('Keterangan', 'like', '%' . request()->q . '%')
                            ->orwhere('Perkiraan', 'like', '%' . request()->q . '%');
                    })
                    ->when(request()->without, function($q) {
                        return $q->where('Perkiraan', '!=', request()->without);
                    })
                    ->where('Tipe', 1)
                    ->orderBy('Perkiraan', 'ASC')
                    ->get()
                    ->map(function ($data) {
                        return [
                            'id' => $data->Perkiraan,
                            'text' => $data->Perkiraan . ' - ' . $data->Keterangan,
                            'Description' => $data->Keterangan
                        ];
                    });

                return response()->json([
                    'results' => $data,
                    'pagination' => [
                        'more' => false
                    ]
                ]);
            }
            return abort(404);
        } catch (\Exception $e) {
            Log::error('Perkiraan select error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

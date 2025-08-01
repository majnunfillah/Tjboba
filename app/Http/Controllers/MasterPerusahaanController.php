<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\DBPERUSAHAANInterface;
use App\Http\Requests\NomorTransaksiRequest;
use App\Http\Requests\PerusahaanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterPerusahaanController extends Controller
{
    private $perusahaanRepository;

    public function __construct(DBPERUSAHAANInterface $perusahaanRepository)
    {
        $this->perusahaanRepository = $perusahaanRepository->model('dbperusahaan');
    }

    public function index()
    {
        $perusahaan = $this->perusahaanRepository->firstOrNew();
        $nomor = $this->perusahaanRepository->model('dbnomor')->firstOrNew();

        return view('berkas.perusahaan', compact('perusahaan', 'nomor'));
    }

    public function update(Request $request)
    {
        if ($request->type === 'perusahaan') {
            $this->perusahaanRepository->updatePerusahaan(app()->make(PerusahaanRequest::class), $request->id);
            return $this->setResponseSuccess();
        } else {
            $this->perusahaanRepository->model('dbnomor')->updateNomor(app()->make(NomorTransaksiRequest::class));
            return $this->setResponseSuccess();
        }
        return abort(404, 'Halaman tidak ditemukan');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\DBDEVISI;
use App\Models\DBMENUREPORT;
use App\Models\DBPERKIRAAN;
use App\Services\DomPDFService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SnappyPDF;

class LaporanController extends Controller
{
    public function viewLaporan()
    {
        $laporan = DBMENUREPORT::where('ACCESS', '<>', '0')->get();

        // Get current period from database or use current date
        $periode = DB::table('dbperiode')
            ->select('BULAN', 'TAHUN')
            ->where('USERID', auth()->user()->USERID)
            ->first() ?? (object)[
                'BULAN' => date('n'),
                'TAHUN' => date('Y')
            ];

        return view('laporan-laporan.laporan', [
            'laporan' => $laporan,
            'periode' => $periode
        ]);
    }

    public function generateLaporan(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $request->validate([
            'access' => ['required', 'numeric'],
        ]);
        $pdfService = new DomPDFService();

        // Get current period
        $periode = DB::table('dbperiode')
            ->select('BULAN', 'TAHUN')
            ->where('USERID', auth()->user()->USERID)
            ->first() ?? (object)[
                'BULAN' => $request->bulan ?? date('n'),
                'TAHUN' => $request->tahun ?? date('Y')
            ];

        $data = [];
        $view = null;
        $marTop = '15mm';
        $marRight = '10mm';
        $marLeft = '10mm';
        $marBottom = '10mm';
        $paper = 'legal';
        $orientation = 'landscape';
        switch ($request->access) {
            case '20501':
                $data = $this->getDataNeracaLajur($request);
                $pdfService->setTitle('Neraca Lajur');
                $pdfService->setLeftHeader(view('components.pdf-service.neraca-lajur.left-header')->render(), '50%');
                $pdfService->setMidHeader(view('components.pdf-service.neraca-lajur.mid-header', [
                    'bulan' => $request->bulan ?? $periode->BULAN,
                    'tahun' => $request->tahun ?? $periode->TAHUN,
                ])->render(), '50%');
                // $pdfService->setRightHeader("&nbsp;");

                if ($request->export_pdf == true) {
                    $pdfService->setBody(view('components.pdf-service.neraca-lajur.body', [
                        'data' => $data,
                    ]));
                }

                $view = view('laporan-laporan.laporan.neraca-lajur', [
                    'headerHtml' => $pdfService->mapHeader(),
                    'data' => $data,
                    'periode' => $periode
                ])->render();
                break;
            case '202021':
                $data = $this->getDataBukuTambahan($request);

                $marTop = "25mm";
                $orientation = 'portrait';
                $perkirawanAwal = DBPERKIRAAN::where('Perkiraan', $request->awal)->first();
                $perkirawanAkhir = DBPERKIRAAN::where('Perkiraan', $request->akhir)->first();

                $pdfService->setTitle('Buku Tambahan');
                $pdfService->setLeftHeader(view('components.pdf-service.buku-tambahan.left-header')->render(), '40%');
                $pdfService->setMidHeader(view('components.pdf-service.buku-tambahan.mid-header', [
                    'perkiraanAwal' => $perkirawanAwal,
                    'perkiraanAkhir' => $perkirawanAkhir,
                    'tglawal' => $request->tglawal,
                    'tglakhir' => $request->tglakhir,
                ])->render(), '60%');
                // dd($data);
                if ($request->export_pdf == true) {
                    $pdfService->setBody(view('components.pdf-service.buku-tambahan.body', [
                        'data' => $data,
                    ]));
                }
                // dd($data);
                $view = view('laporan-laporan.laporan.buku-tambahan', [
                    'headerHtml' => $pdfService->mapHeader(),
                    'data' => $data
                ])->render();
                break;
            case '20502':
                $data = $this->getDataLabaRugi($request);

                $marTop = "25mm";
                $orientation = 'portrait';


                $pdfService->setTitle('Laba Rugi');
                $pdfService->setLeftHeader(view('components.pdf-service.laba-rugi.left-header')->render(), '40%');
                $pdfService->setMidHeader(view('components.pdf-service.laba-rugi.mid-header', [
                    'bulan' => $request->bulan ?? $periode->BULAN,
                    'tahun' => $request->tahun ?? $periode->TAHUN,
                ])->render(), '60%');
                // dd($data);
                if ($request->export_pdf == true) {
                    $pdfService->setBody(view('components.pdf-service.laba-rugi.body', [
                        'data' => $data,
                    ]));
                }
                // dd($data);
                $view = view('laporan-laporan.laporan.laba-rugi', [
                    'headerHtml' => $pdfService->mapHeader(),
                    'data' => $data
                ])->render();
                break;
            case '101': // N10N101rt accessN101de
                $data= $this->getDataDaftarPerkiraan($request);
                $marTop = "25mm";
                $orientation = 'portrait';

                $pdfService->setTitle('Daftar Perkiraan');
                $pdfService->setLeftHeader(view('components.pdf-service.DaftarPerkiraan.left-header')->render(), '40%');
                $pdfService->setMidHeader(view('components.pdf-service.DaftarPerkiraan.mid-header', [])->render(), '60%');

                if ($request->export_pdf == true) {
                    $pdfService->setBody(view('components.pdf-service.DaftarPerkiraan.body', [
                        'data' => $data,
                    ]));
                }
                //dd($data);
                $view = view('laporan-laporan.laporan.DaftarPerkiraan', [
                    'headerHtml' => $pdfService->mapHeader(),
                    'data' => $data
                ])->render();
                break;

        }

        if ($request->export_pdf == true) {
            // return $pdfService->outputView();
            $pdf = SnappyPDF::loadHTML($pdfService->outputHtml());
            // $pdf = SnappyPDF::loadHTML("<h1>Hello</h1>");
            $pdf->setPaper($paper);
            $pdf->setOrientation($orientation);
            $pdf->setOption('margin-top', $marTop);
            $pdf->setOption('margin-left', $marLeft);
            $pdf->setOption('margin-right', $marRight);
            $pdf->setOption('margin-bottom', $marBottom);
            $pdf->setOption('header-html', $pdfService->mapHeader()->render());
            // $pdf->setOption('footer-html', $footerHtml);
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setTemporaryFolder(storage_path('app/public/temp'));
            return $pdf->stream('table.pdf');

            // $dompdf = $pdfService->outputDomPdf();
            // $dompdf->setPaper('legal', 'landscape')
            //     ->setOption('enable_php', true);
            // $dompdf->output();
            // $dom_pdf = $dompdf->getDomPDF();

            // $canvas = $dom_pdf->getCanvas();
            // $canvas->page_text(0, 0, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", null, 10, array(0, 0, 0));

            // return response($dompdf->output(), 200)
            //     ->header('Content-Type', 'application/pdf')
            //     ->header('Content-Disposition', 'inline; filename="document.pdf"');
        } else {
            return response()->json([
                'status' => 200,
                'html' => $view,
            ]);
        }
    }

    private function getDataNeracaLajur(Request $request)
    {
        $request->validate([
            'devisi' => ['required', 'string'],
            'bulan' => ['required', 'numeric'],
            'tahun' => ['required', 'numeric'],
        ]);

        return DB::select("EXEC sp_NerajaLajur ?,?,?,?,?", ['', $request->bulan, $request->tahun, $request->devisi, auth()->user()->USERID]);
    }

    private function getDataBukuTambahan(Request $request)
    {
        $request->validate([
            'devisi' => ['required', 'string'],
            'awal' => ['required'],
            'akhir' => ['required'],
            'tglawal' => ['required', 'date'],
            'tglakhir' => ['required', 'date'],
            'jurnal' => ['required', 'in:Y,T'],
        ]);

        return DB::select("EXEC Sp_ReportBukuTambahan ?,?,?,?,?,?,?", [$request->awal, $request->akhir, $request->tglawal, $request->tglakhir, $request->devisi, auth()->user()->USERID, $request->jurnal]);
    }

    private function getDataLabaRugi(Request $request)
    {
        $request->validate([
            'devisi' => ['required', 'string'],
            'bulan' => ['required', 'numeric'],
            'tahun' => ['required', 'numeric'],
        ]);

        $rhpp = DB::select("select totalA,totalB,totalC from dbLRHPP rh where rh.Persen = 'A' and rh.Tahun = ? AND rh.Bulan = ? AND rh.Devisi = ? ", [$request->tahun, $request->bulan, $request->devisi]);
        if (count($rhpp) == 0) {
            return [];
        }
        $data = DB::select("EXEC Sp_ReportLabaRugi ?,?,?,?,?,?,?", [$request->bulan, $request->tahun, $request->devisi, $request->access == '20505' ? 1 : 0, $rhpp[0]->totalA, $rhpp[0]->totalB, $rhpp[0]->totalC]);
        return $data;
        // EXECUTE  [Sp_ReportLabaRugi] @Bulan   ,@tahun   ,@devisi   ,@prosesRlHpp  ,@jumlahA   ,@jumlahB  ,@jumlahC
    }

    private function getDataDaftarPerkiraan(Request $request)
    {
        //\Log::info('Getting Daftar Perkiraan data');
           return DB::select("SELECT * FROM vwPerkiraan a order by a.Perkiraan", [auth()->user()->USERID]);
            // Debug hasil query
            //$data = DB::select("SELECT * FROM vwPerkiraan a order by a.Perkiraan");
   // \Log::info('Query result:', ['count' => count($data)]);
    }
}

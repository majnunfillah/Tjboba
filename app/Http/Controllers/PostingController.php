<?php

/**
 * PostingController handles functionalities related to posting data.
 */
namespace App\Http\Controllers;

use /**
 * Interface GlobalInterface
 *
 * This interface defines the contract for global task operations within the application.
 * It facilitates a consistent implementation of task-related methods across different classes.
 *
 * @package App\Http\Repository\Task
 */
    App\Http\Repository\Task\GlobalInterface;
use /**
 * Class CustomDataTable
 *
 * This service class is responsible for handling the custom implementation
 * of data table integration within the Laravel application. It enables
 * efficient management and manipulation of data for display in a tabular format.
 *
 * Responsibilities include:
 * - Processing server-side data fetching and filtering.
 * - Structuring data for consumption by the front-end.
 * - Providing sorting, pagination, and search functionality.
 *
 * Dependencies:
 * - Utilizes the SQL Server (sqlsrv) database connection.
 * - Operates with queue connection set to "sync".
 *
 * Usage:
 * Intended to be invoked as a service within controllers or other application layers where
 * customized data table processing is required.
 */
    App\Http\Services\CustomDataTable;
use /**
 * Class Request
 *
 * Represents an incoming HTTP request in a Laravel application. It provides methods for interacting with
 * the HTTP request's data, such as input values, cookies, files, and headers.
 *
 * This class extends Symfony's HTTP foundation request and includes additional methods tailored for
 * Laravel's framework capabilities. It also offers data manipulation, validation, and helper methods
 * to access route parameters or session data.
 *
 * Commonly utilized in controller methods to retrieve, validate, or manipulate incoming request data.
 *
 * @package Illuminate\Http
 */
    Illuminate\Http\Request;

/**
 * Controller responsible for handling posting-related operations.
 */
class PostingController extends Controller
{
    private $globalRepository;
    private $access;



    public function __construct(GlobalInterface $globalRepository)
    {
        $this->globalRepository = $globalRepository;
        $this->middleware(function ($request, $next) {
            $this->access = auth()->user()->getPermissionsName('01001008');
            return $next($request);
        });
    }

    public function posting()
    {   // \Log::info('Posting method called');
        $postings = [
            ["cardId" => "KAS", "cardName" => "Table Kas", "cardIcon" => "fa fa-money-bill-wave", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "BANK", "cardName" => "Table Bank", "cardIcon" => "fa fa-university", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "AKUMULASI", "cardName" => "Table Akumulasi", "cardIcon" => "fa fa-calculator", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "AKTIVA", "cardName" => "Table Aktiva", "cardIcon" => "fa fa-book", "cardComponent" => "aktiva", "modalWidth" => "xl"],
            ["cardId" => "HARGAPOKOK", "cardName" => "Harga Pokok", "cardIcon" => "fa fa-receipt", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "PIUTANG", "cardName" => "Piutang", "cardIcon" => "fa fa-book", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "HUTANG", "cardName" => "Hutang", "cardIcon" => "fa fa-times","cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "DEPOSITO", "cardName" => "Deposito", "cardIcon" => "fa fa-times"],
            ["cardId" => "UMPIUTANG", "cardName" => "UM Piutang", "cardIcon" => "fa fa-times"],
            ["cardId" => "UMHUTANG", "cardName" => "UM Hutang", "cardIcon" => "fa fa-times"],
            ["cardId" => "PIUTANGSEMENTARA", "cardName" => "Piutang Sementara", "cardIcon" => "fa fa-times"],
            ["cardId" => "HUTANGSEMENTARA", "cardName" => "Hutang Sementara", "cardIcon" => "fa fa-times"],
            ["cardId" => "RLTAHUNLALU", "cardName" => "RL Tahun Lalu", "cardIcon" => "fa fa-money-bill-wave", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "RLTAHUNINI", "cardName" => "RL Tahun Ini", "cardIcon" => "fa fa-times"],
            ["cardId" => "RLBULANINI", "cardName" => "RL Bulan Ini", "cardIcon" => "fa fa-times"],
            ["cardId" => "SELISIH", "cardName" => "Selisih", "cardIcon" => "fa fa-times"],
            ["cardId" => "BIAYADEBET", "cardName" => "Biaya Debet Note", "cardIcon" => "fa fa-times"],
            ["cardId" => "BIAYAKREDIT", "cardName" => "Biaya Kredit Note", "cardIcon" => "fa fa-times"],
            ["cardId" => "BIAYAOPNAME", "cardName" => "Biaya Opname", "cardIcon" => "fa fa-times"],
            ["cardId" => "WIP", "cardName" => "W I P", "cardIcon" => "fa fa-times"],
            ["cardId" => "PENDAPATAN", "cardName" => "Pendapatan", "cardIcon" => "fa fa-times"],
            ["cardId" => "PPNMASUKAN", "cardName" => "PPN Masukan", "cardIcon" => "fa fa-times", "cardComponent" => "kas", "modalWidth" => "md"],
            ["cardId" => "PPNKELUARAN", "cardName" => "PPN Keluaran", "cardIcon" => "fa fa-times"],
            ["cardId" => "PPHMASUKAN", "cardName" => "PPH Masukan", "cardIcon" => "fa fa-times"],
            ["cardId" => "PPHKELUARAN", "cardName" => "PPH Keluaran", "cardIcon" => "fa fa-times"],

        ];
        return view('master_data.master_accounting.posting', [
            'postings' => $postings
        ]);
    }

    public function getAllKelompokKas($kode)
    {
        $this->requestAjax($this->access, 'HASACCESS');
        //\Log::info('Getting data for kode: ' . $kode);
        return CustomDataTable::init()
            ->of($this->globalRepository->getKelompokKasOrBank($kode))
            ->mapData(function ($data) {
                $data->Perkiraan = trim($data->Perkiraan);
                $data->Keterangan = trim($data->Keterangan);
                return $data;
            })
            ->apply()
            ->addColumn('action', function ($data) use ($kode) {
                $button = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                <div class="notification-container close-button-container">';
                if (in_array('ISKOREKSI', $this->access)) {
                    $button .= "<button class='btn btn-warning btn-sm mr-1 btnEditPostingKas' data-perkiraan='{$data->Perkiraan}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                }
                if (in_array('ISHAPUS', $this->access)) {
                    $url = route('master-data.master-accounting.posting.deletePosting', ['posting' => $kode, 'id' => $data->Perkiraan]);
                    $button .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->Perkiraan}' data-key='{$data->Perkiraan}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                }
                $button .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                return $button;
            })->done();
    }

    public function getAllKelompokAktiva()
    {
        $this->requestAjax($this->access, 'HASACCESS');
        return CustomDataTable::init()
            ->of($this->globalRepository->getKelompokAktiva())
            ->apply()
            ->addColumn('action', function ($data) {
                $button = '<div style="max-width: 100%; position: relative; width: 1px; height: 1px; margin: auto;">
                <div class="notification-container close-button-container">';
                if (in_array('ISKOREKSI', $this->access)) {
                    $button .= "<button class='btn btn-warning btn-sm mr-1 btnEditPosting' data-perkiraan='{$data->Perkiraan}'><i class='fa fa-pen mr-1'></i>Edit</button>";
                }
                if (in_array('ISHAPUS', $this->access)) {
                    $url = route('master-data.master-accounting.posting.deletePosting', ['posting' => 'AKTIVA', 'id' => $data->Perkiraan]);
                    $button .= "<button class='btn btn-danger btn-sm mr-1 btnGlobalDelete' data-url='{$url}' data-id='{$data->Perkiraan}' data-key='{$data->Perkiraan}'><i class='fa fa-trash mr-1'></i>Hapus</button>";
                }
                $button .= '</div></div><button type="button" class="btn btn-primary btn-sm showButton" id=""><i class="fa fa-arrow-alt-circle-left"></i></button>';
                return $button;
            })->done();
    }

    public function getTable($kode)
    {
        switch ($kode) {
            case 'KAS':
                return $this->getAllKelompokKas($kode);
                break;
            case 'BANK':
                return $this->getAllKelompokKas($kode);
                break;
            case 'AKUMULASI':
                return $this->getAllKelompokKas('AKM');
                break;
            case 'AKTIVA':
                return $this->getAllKelompokAktiva();
                break;
            case 'HARGAPOKOK':
                return $this->getAllKelompokKas('HPP');
                break;
            case 'PIUTANG': // Add this case
                return $this->getAllKelompokKas('PT');
                break;
            case 'HUTANG': // Add this case
                return $this->getAllKelompokKas('HT');
                break;
            case 'PPNMASUKAN': // Add this case
                return $this->getAllKelompokKas('PPM');
                break;

            default:
                return $this->setResponseError('Halaman tidak ditemukan', 500);
                break;
        }
    }

    public function getModalPosting($kode, $component)
    {
        switch ($kode) {
            case 'KAS':
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Kas',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingKAS')"
                ];
                break;
            case 'BANK':
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Bank',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingBANK')"
                ];
                break;
            case 'AKUMULASI':
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Akumulasi',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingAKUMULASI')"
                ];
                break;
            case 'AKTIVA':
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Aktiva',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingAktiva()"
                ];
                break;
            case 'HARGAPOKOK':
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Harga Pokok Penjualan',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingHARGAPOKOK')"
                ];
            case 'PIUTANG': // Add this case
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Piutang',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingPIUTANG')"];
                break;
            case 'HUTANG': // Tambahkan case HUTANG
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok Hutang',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingHUTANG')"
                ];
                break;

            case 'PPNMASUKAN': // Tambahkan case PPNMASUKAN
                $url = route('master-data.master-accounting.posting.getTable', ['posting' => $kode]);
                return [
                    'formAction' => $url,
                    'modalTitle' => 'Kelompok PPN Masukan',
                    'component' => $component,
                    'datatableUrl' => $url,
                    'callback' => "postingKAS('formPostingPPNMASUKAN')"
                ];
                break;

                //dd($response);
                return $response;

            default:
                return abort(404, 'Halaman tidak ditemukan ');
                break;
        }
    }

    public function storePosting(Request $request, $posting)
    {
        $this->requestAjax($this->access, 'ISKOREKSI');
        switch ($posting) {
            case 'KAS':
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'KAS')) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'BANK':
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'BANK')) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'AKUMULASI':
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'AKM')) {
                    return $this->setResponseSuccess();
                }
            case 'AKTIVA':
                if ($this->globalRepository->storeKelompokAktiva($request, $request->oldPerkiraan)) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'HARGAPOKOK':
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'HPP')) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'PIUTANG': // Add this case
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'PT')) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'HUTANG': // Add this case
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'HT')) {
                    return $this->setResponseSuccess();
                }
                break;
            case 'PPNMASUKAN': // Add this case
                if ($this->globalRepository->storeKelompokKasOrBank($request->Perkiraan, $request->oldPerkiraan, 'PPM')) {
                    return $this->setResponseSuccess();
                }
                break;


        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }

    /**
     * Handles deletion of specified postings based on the type and ID.
     *
     * This function processes a delete request for different types of postings.
     * Depending on the $posting type provided ('KAS', 'BANK', 'AKM', 'AKTIVA', 'HARGAPOKOK'),
     * it delegates the deletion task to the appropriate method in the global repository and
     * returns a success response if the deletion is successful.
     *
     * If the specified posting type is not found or the deletion fails, a generic error response
     * is returned.
     *
     * @param string $posting The type of posting to be deleted (e.g., 'KAS', 'BANK', etc.).
     * @param int $id The unique identifier of the posting to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse The response indicating the success or failure of the deletion.
     */
    public function deletePosting($posting, $id)
    {
        $this->requestAjax($this->access, 'ISHAPUS');
        try {
            switch ($posting) {
                case 'KAS':
                    if ($this->globalRepository->deleteKelompokKasOrBank($id, 'KAS')) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    break;
                case 'BANK':
                    if ($this->globalRepository->deleteKelompokKasOrBank($id, 'BANK')) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    break;
                case 'AKM':
                    if ($this->globalRepository->deleteKelompokKasOrBank($id, 'AKM')) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    break;
                case 'AKTIVA':
                    if ($this->globalRepository->deleteKelompokAktiva($id)) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    break;
                case 'HPP':
                    if ($this->globalRepository->deleteKelompokKasOrBank($id, 'HPP')) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    /* try {
                         \Log::info('Testing HPP deletePosting flow');
                     } catch (\Exception $e) {
                         echo $e->getMessage(); // Print error details if logging fails.
                     }*/
                    break;
                case 'PT': // Add this case
                if ($this->globalRepository->deleteKelompokKasOrBank($id, 'PT')) {
                    return $this->setResponseData(['datatable' => 'datatableMain']);
                }
                break;
                case 'HT': // Add this case
                if ($this->globalRepository->deleteKelompokKasOrBank($id, 'HT')) {
                    return $this->setResponseData(['datatable' => 'datatableMain']);
                }
                break;

                case 'PPM': // Add this case
                    if ($this->globalRepository->deleteKelompokKasOrBank($id, 'PPM')) {
                        return $this->setResponseData(['datatable' => 'datatableMain']);
                    }
                    break;
            }
        }  catch (\Exception $e) {
            \Log::error('Error while deleting posting: ' . $e->getMessage());
            return $this->setResponseError('An error occurred while processing your request', 500);
        }
        return $this->setResponseError('Halaman tidak ditemukan', 500);
    }
}

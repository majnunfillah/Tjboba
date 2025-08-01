<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function setResponseSuccess($message = 'Berhasil menyimpan / merubah data ', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ], $code);
    }

    public function setResponseData($data, $message = "Berhasil menyinmpan / merubah data ", $code = 200)
    {
        return response()->json([
            "message" => $message,
            'status' => true,
            'data' => $data,
        ], $code);
    }

    public function  setResponseError($message = 'Gagal menyimpan / merubah data ', $code = 501)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }

    public function requestAjax($access = null, $can = null)
    {
        if (request()->ajax()) {
            if($access != null){
                if (in_array($can, $access)) {
                    return true;
                }
                return $this->setResponseError('Anda tidak memiliki akses untuk operasi ini', 403);
            }else{
                return true;
            }
        }
        return abort(404, 'Halaman tidak ditemukan');
    }

}

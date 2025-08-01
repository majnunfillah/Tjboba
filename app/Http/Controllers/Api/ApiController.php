<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repository\Task\GlobalInterface;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    private $globalRepository;

    public function __construct(GlobalInterface $globalRepository)
    {
        $this->globalRepository = $globalRepository;
    }

    public function getDataBarang(Request $request)
    {   
        $this->decodeApiKey($request);

        $data = $this->globalRepository->queryModel('dbbarang')->paginate(500);

        return $this->setResponseData($data, "Data barang berhasil diambil");
    }

    private function decodeApiKey($request)
    {
        if(!$request->has('api_key'))
        {
            return $this->setResponseError('Api key is required', 422);
        }
        
        $c = base64_decode($request->api_key);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, "tew2022", $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, "tew2022", $as_binary=true);
        if (!hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
        {
            return $this->setResponseError("Api key is invalid", 422);
        }
        
        if($original_plaintext == "trade exchange website 2022")
        {
            return true;
        }
        
        return $this->setResponseError("Api key is invalid", 422);
    }
}

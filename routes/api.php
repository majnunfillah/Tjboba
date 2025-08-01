<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
  return 'oke';
});
Route::get('get-data-barang', [ApiController::class, 'getDataBarang']);

Route::post('login', function () {
  return 'check';
});
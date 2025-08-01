<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrowseController;

Route::get('/', function () {
    return view('welcome');
});

// Browse routes
Route::prefix('browse')->group(function () {
    Route::get('/', [BrowseController::class, 'index'])->name('browse.index');
    Route::post('/search', [BrowseController::class, 'search'])->name('browse.search');
    Route::post('/get-data', [BrowseController::class, 'getData'])->name('browse.getData');
}); 
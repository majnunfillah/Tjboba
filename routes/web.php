<?php

use App\Http\Controllers\{
    AktivaController,
    AuthController,
    BankOrKasController,
    MemorialController,
    GroupController,
    LaporanController,
    MasterPerusahaanController,
    ModalController,
    PerkiraanController,
    PostingController,
    SetPemakaiController,
    ShareController,
    TestController,
    MasterMenuController,
    TutupBukuController,
    SPKController,
};
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'loginView'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    //Menu Berkas
    Route::prefix('berkas')->name('berkas.')->group(function () {
        //perusahaan
        Route::get('perusahaan', [MasterPerusahaanController::class, 'index'])->name('perusahaan.index')->middleware('policy:HASACCESS,0003');
        Route::post('perusahaan/{type}', [MasterPerusahaanController::class, 'update'])->name('perusahaan.update')->where('type', 'perusahaan|nomor')->middleware('policy:ISKOREKSI,0003');
        // Menu
        Route::get('menumaster', [MasterMenuController::class, 'index'])->name('mastermenu.index')->middleware('policy:HASACCESS,00031');

        //set pemakai
        Route::get('set-pemakai', [SetPemakaiController::class, 'index'])->name('set-pemakai.index')->middleware('policy:HASACCESS,0004');
        Route::put('set-pemakai/{USERID}', [SetPemakaiController::class, 'update'])->name('set-pemakai.update')->middleware('policy:ISKOREKSI,0004');
        Route::put('set-pemakai-coa/{USERID}', [PerkiraanController::class, 'updateCOA'])->name('set-pemakai-coa.update')->middleware('policy:ISKOREKSI,0004');
        Route::post('set-pemakai-karyawan', [SetPemakaiController::class, 'createKaryawan'])->name('set-pemakai-karyawan.create')->middleware('policy:ISTAMBAH,0004');
        Route::put('set-pemakai-karyawan/{USERID}', [SetPemakaiController::class, 'updateKaryawan'])->name('set-pemakai-karyawan.update')->middleware('policy:ISKOREKSI,0004');
        Route::delete('set-pemakai-karyawan/{USERID}', [SetPemakaiController::class, 'deleteKaryawan'])->name('set-pemakai-karyawan.delete')->middleware('policy:ISHAPUS,0004');

        Route::get('get-periode', [ShareController::class, 'getPeriode'])->name('get-periode')->middleware('policy:HASACCESS,0001');
        Route::put('set-periode', [ShareController::class, 'setPeriode'])->name('set-periode')->middleware('policy:ISKOREKSI,0001');
    });

    //Menu Master Data
    Route::prefix('master-data')->name('master-data.')->group(function () {
        //master Accounting
        Route::prefix('master-accounting')->name('master-accounting.')->group(function () {
            //master perkiraan
            Route::resource('perkiraan', PerkiraanController::class)->names('perkiraan')->middleware('policy:HASACCESS,01001001');
            Route::post('get-saldo-awal/{perkiraan}', [PerkiraanController::class, 'getSaldoAwal'])->name('get-saldo-awal')->middleware('policy:HASACCESS,01001001');
            Route::post('set-saldo-awal/{perkiraan}', [PerkiraanController::class, 'setSaldoAwal'])->name('set-saldo-awal.update')->middleware('policy:ISKOREKSI,01001001');
            Route::post('get-budget/{perkiraan}', [PerkiraanController::class, 'getBudget'])->name('get-budget')->middleware('policy:HASACCESS,01001001');
            Route::post('set-budget/{perkiraan}', [PerkiraanController::class, 'setBudget'])->name('set-budget.update')->middleware('policy:ISKOREKSI,01001001');

            //master aktiva
            Route::resource('aktiva', AktivaController::class)->names('aktiva')->middleware('policy:HASACCESS,01001002')->only(['index', 'store']);
            Route::put('aktiva/{aktiva}/{devisi}', [AktivaController::class, 'update'])->name('aktiva.update');
            Route::delete('aktiva/{aktiva}/{devisi}', [AktivaController::class, 'destroy'])->name('aktiva.destroy');
            Route::get('aktiva/{aktiva}/{devisi}', [AktivaController::class, 'getSaldoAwal'])->name('aktiva.saldo-awal');
            Route::post('aktiva/{aktiva}/{devisi}', [AktivaController::class, 'setSaldoAwal']);

            //master posting
            Route::get('posting', [PostingController::class, 'posting'])->name('posting.index')->middleware('policy:HASACCESS,01001008');
            Route::get('posting/{posting}', [PostingController::class, 'getTable'])->name('posting.getTable');
            Route::post('posting/{posting}', [PostingController::class, 'storePosting']);
            Route::delete('posting/{posting}/{id}', [PostingController::class, 'deletePosting'])->name('posting.deletePosting');
        });
        //master Bahan dan Barang
        Route::prefix('master-bahan-barang')->name('master-bahan-dan-barang.')->group(function () {
            //master Goup
            Route::resource('group', GroupController::class)->names('group')->middleware('policy:HASACCESS,01002015')->only(['index', 'destroy', 'store', 'update']);
            // sub group
            Route::get('{group}/sub', [GroupController::class, 'getSubGroup'])->name('sub-group');
            Route::post('{group}/sub', [GroupController::class, 'storeSubGroup']);
            Route::post('{group}/sub/{subgroup}', [GroupController::class, 'updateSubGroup']);
            Route::delete('{group}/sub/{subgroup}/destroy', [GroupController::class, 'deleteSubGroup'])->name('sub-group.destroy');
            // departrmen sub group
            Route::get('{group}/sub/{subgroup}/departemen', [GroupController::class, 'getDepartemen'])->name('sub-group.departemen');
            Route::post('{group}/sub/{subgroup}/departemen', [GroupController::class, 'storeDepartemen']);
            Route::post('{group}/sub/{subgroup}/departemen/{KodeDepartemen}', [GroupController::class, 'updateDepartemen']);
            Route::delete('{group}/sub/{subgroup}/departemen/{KodeDepartemen}/destroy', [GroupController::class, 'deleteDepartemen'])->name('sub-group.departemen.destroy');
        });
    });

    Route::prefix('accounting')->name('accounting')->group(function () {
        // transaksi bank or kas
        Route::prefix('transaksi-bank-or-kas')->name('.bank-or-kas')->middleware('policy:HASACCESS,02001')->group(function () {
            Route::get('/', [BankOrKasController::class, 'index'])->name('.index');
            Route::post('/', [BankOrKasController::class, 'store']);
            Route::put('/', [BankOrKasController::class, 'update']);
            Route::delete('/', [BankOrKasController::class, 'delete'])->name('.delete');
            Route::post('/detail', [BankOrKasController::class, 'getKasBankDetailByNoBukti'])->name('.detail-kasbank');
            Route::get('/download-kasbank', [BankOrKasController::class, 'downloadKasBank']);
            Route::post('/get-nomor-bukti', [BankOrKasController::class, 'getNomorBukti']);
            Route::post('/get-data-hutang', [BankOrKasController::class, 'getDataHutang'])->name('.get-data-hutang');
            Route::post('/kas-bank-detail', [BankOrKasController::class, 'storeKasbank']);
            Route::put('/kas-bank-detail', [BankOrKasController::class, 'updateKasBank']);
            Route::delete('/kas-bank-detail', [BankOrKasController::class, 'deleteKasBank'])->name('.delete-kasbank');
            Route::post('/set-otorisasi', [BankOrKasController::class, 'setOtorisasi']);
            Route::post('/pelunasan-hutang', [BankOrKasController::class, 'pelunasanHutang'])->name('.pelunasan-hutang');
            Route::post('/hapus-pelunasan', [BankOrKasController::class, 'hapusPelunasan'])->name('.hapus-pelunasan');
        });

        // transaksi Memorial
        Route::prefix('transaksi-memorial')->name('.memorial')->middleware('policy:HASACCESS,02002')->group(function () {
            Route::get('/', [MemorialController::class, 'index'])->name('.index');
            Route::post('/', [MemorialController::class, 'store']);
            Route::put('/', [MemorialController::class, 'update']);
            Route::delete('/', [MemorialController::class, 'delete'])->name('.delete');
            Route::post('/detail', [MemorialController::class, 'getMemorialDetailByNoBukti'])->name('.detail-memorial');
            Route::get('/download-memorial', [MemorialController::class, 'downloadMemorial']);
            Route::post('/get-nomor-bukti', [MemorialController::class, 'getNomorBukti']);
            Route::post('/get-data-hutang', [MemorialController::class, 'getDataHutang'])->name('.get-data-hutang');
            Route::post('/memorial-detail', [MemorialController::class, 'storeMemorial']);
            Route::get('/memorial-detail', [MemorialController::class, 'getDetailMemorialByNoBukti'])->name('.get-detail-memorial');
            Route::put('/memorial-detail', [MemorialController::class, 'updateMemorial']);
            Route::delete('/memorial-detail', [MemorialController::class, 'deleteMemorial'])->name('.delete-memorial');
            Route::post('/set-otorisasi', [MemorialController::class, 'setOtorisasi']);
            Route::post('/pelunasan-hutang', [MemorialController::class, 'pelunasanHutang'])->name('.pelunasan-hutang');
            Route::post('/hapus-pelunasan', [MemorialController::class, 'hapusPelunasan'])->name('.hapus-pelunasan');
        });
    });

    Route::prefix('POS')->name('pos')->group(function () {
        // Route::get('/')
    });

    //Menu Utilitas
    Route::prefix('utilitas')->name('utilitas.')->group(function () {
        Route::get('tutup-buku', [TutupBukuController::class, 'index'])->name('tutup-buku.index');
        Route::post('tutup-buku/proses', [TutupBukuController::class, 'proses'])->name('tutup-buku.proses');
        // Route::get('tutup-buku/progress/{bulan}/{tahun}', [TutupBukuController::class, 'getProgress'])->name('tutup-buku.progress'); // For future real-time progress

        // Hitung Ulang HPP Routes
        Route::prefix('hitung-ulang-hpp')->name('hitung-ulang-hpp.')->group(function () {
            Route::get('/', [App\Http\Controllers\HitungUlangHPPController::class, 'index'])->name('index');
            Route::post('/proses', [App\Http\Controllers\HitungUlangHPPController::class, 'prosesHitungUlangHPP'])->name('proses');
            Route::post('/execute', [App\Http\Controllers\HitungUlangHPPController::class, 'executeHitungUlangHPP'])->name('execute');
            Route::post('/get-stock-minus', [App\Http\Controllers\HitungUlangHPPController::class, 'getStockMinusData'])->name('get-stock-minus');
            Route::get('/export', [App\Http\Controllers\HitungUlangHPPController::class, 'exportStockMinus'])->name('export');
        });
    });

    //global
    Route::prefix('/')->group(function () {
        Route::put('ganti-password', [AuthController::class, 'gantiPassword'])->name('ganti-password');
        Route::get('get-karyawan-select', [ShareController::class, 'getKaryawanSelect']);
        Route::get('get-departemen-select', [ShareController::class, 'getDepartemenSelect']);
        Route::get('get-jabatan-select', [ShareController::class, 'getJabatanSelect']);
        Route::get('get-valas-select', [ShareController::class, 'getValasSelect']);
        Route::get('get-arus-kas-select', [ShareController::class, 'getArusKasSelect']);
        Route::get('get-arus-kas-det-select', [ShareController::class, 'getArusKasDetSelect']);
        Route::get('get-group-aktiva-select', [ShareController::class, 'getGroupAktivaSelect']);
        Route::get('get-devisi-select', [ShareController::class, 'getDevisiSelect']);
        Route::get('get-akumulasi-penyusutan-select', [ShareController::class, 'getAkumulasiPenyusutanSelect']);
        Route::get('get-biaya-select', [ShareController::class, 'getBiayaSelect']);
        Route::get('get-perkiraan-select', [ShareController::class, 'getPerkiraanSelect']);
        Route::get('get-kelompok-kas-select', [ShareController::class, 'getKelompokKasSelect']);
        Route::get('get-kelompok-kas-bank-select', [ShareController::class, 'getKelompokKasOrBankSelect']);

        Route::get('get-user/{USERID}', [ShareController::class, 'getUser'])->name('get-user');
        Route::get('get-user-access/{USERID}', [ShareController::class, 'getUserAccess'])->name('get-user-access');

        Route::get('get-customer-hutang', [ShareController::class, 'getCustomerHutang'])->name('get-customer-hutang');
        // routing modal
        Route::post('get-modal', [ModalController::class, 'getModal'])->name('get-modal');
    });

    Route::prefix('/laporan-laporan')->name('laporan-laporan.')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'viewLaporan'])->name('view-laporan');
        Route::post('/laporan', [LaporanController::class, 'generateLaporan']);
        Route::get('/laporan-pdf', [LaporanController::class, 'generateLaporan'])->name('generate-laporan-pdf');
    });



    // SPK Routes
    // PRODUKSI GROUP
    Route::prefix('produksi')->name('produksi.')->group(function () {
        // SPK SUBGROUP
        Route::prefix('transaksi-spk')->name('spk')->middleware('policy:HASACCESS,08103')->group(function () {
            Route::get('/', [SPKController::class, 'index'])->name('.index');
            Route::delete('/', [SPKController::class, 'delete'])->name('.delete');
            Route::get('/rpp-data', [SPKController::class, 'getRppData'])->name('.rpp-data');
            Route::post('/store', [SPKController::class, 'store'])->name('.store');
            Route::post('/detail', [SPKController::class, 'getSpkDetailByNoBukti'])->name('.detail-Spk');
            Route::get('/detail-debug', [SPKController::class, 'getSpkDetailByNoBukti'])->name('.detail-debug');
            Route::get('/test-detail', [SPKController::class, 'testSpkDetail'])->name('.test-detail');
            Route::post('/set-otorisasi', [SPKController::class, 'setOtorisasi'])->name('.set-otorisasi');

            // Detail CRUD routes
            Route::get('/detail/create', [SPKController::class, 'createDetail'])->name('.detail.create');
            Route::post('/detail/store', [SPKController::class, 'storeDetail'])->name('.detail.store');
            Route::get('/detail/edit', [SPKController::class, 'editDetail'])->name('.detail.edit');
            Route::post('/detail/update', [SPKController::class, 'updateDetail'])->name('.detail.update');
            Route::post('/detail/delete', [SPKController::class, 'deleteDetail'])->name('.detail.delete');

            // Jadwal CRUD routes
            Route::get('/jadwal/create', [SPKController::class, 'createJadwal'])->name('.jadwal.create');
            Route::post('/jadwal/store', [SPKController::class, 'storeJadwal'])->name('.jadwal.store');
            Route::get('/jadwal/edit', [SPKController::class, 'editJadwal'])->name('.jadwal.edit');
            Route::post('/jadwal/update', [SPKController::class, 'updateJadwal'])->name('.jadwal.update');
            Route::post('/jadwal/delete', [SPKController::class, 'deleteJadwal'])->name('.jadwal.delete');

            // Level 2 routes
            Route::get('/detail-level2', [SPKController::class, 'getSpkDetailLevel2ByNoBukti'])->name('.detail-level2');
            Route::get('/detail-level2-all', [SPKController::class, 'getSpkDetailLevel2AllByNoBukti'])->name('.detail-level2-all');
            Route::delete('/delete-level2', [SPKController::class, 'deleteSpkDetailLevel2'])->name('.delete-level2');
            Route::post('/store-level2', [SPKController::class, 'storeSpkDetailLevel2'])->name('.store-level2');
            Route::post('/update-level2', [SPKController::class, 'updateSpkDetailLevel2'])->name('.update-level2');

            // Debug route for level 2
            Route::get('/debug-level2', [SPKController::class, 'debugSpkDetailLevel2'])->name('.debug-level2');

            // Level 3 routes
            Route::get('/detail-level3', [SPKController::class, 'getSpkDetailLevel3ByNoBukti'])->name('.detail-level3');
            Route::delete('/delete-level3', [SPKController::class, 'deleteSpkDetailLevel3'])->name('.delete-level3');
            Route::post('/store-level3', [SPKController::class, 'storeSpkDetailLevel3'])->name('.store-level3');
            Route::post('/update-level3', [SPKController::class, 'updateSpkDetailLevel3'])->name('.update-level3');

            // Outstanding SO routes (memorial style)
            Route::get('/outstanding-so', [SPKController::class, 'outstandingSo'])->name('.outstanding-so');
            //Route::get('/outstanding-so-summary', [SPKController::class, 'outstandingSoSummary'])->name('.outstanding-so-summary');

            // Statistics route
            Route::get('/statistics', [SPKController::class, 'statistics'])->name('.statistics');
        });
        // Tambahkan submodul lain di bawah produksi di sini jika diperlukan
    });


});



Route::get('/tester-query', TestController::class);

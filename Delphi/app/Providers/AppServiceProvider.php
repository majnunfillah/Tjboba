<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register global variables from MyGlobal.pas
        $this->app->singleton('global.vars', function () {
            return [
                'kodeForm' => 0,
                'isMax' => 0,
                'gFlagMenu' => 0,
                'kodeBrows' => 0,
                'kodeBrows2' => 0,
                'statusUser' => 0,
                'nNamaCust' => '',
                'nomorBukti' => '',
                'idUser' => '',
                'periodBln' => '',
                'periodThn' => '',
                'tempCode' => '',
                'xInisialPNJ' => '',
                'xInisialTrans' => '',
                'noBuktiBeli' => '',
                'alamatGdg' => '',
                'xnamaGudang' => '',
                'setUpTgl' => null,
                'koneksi' => [],
                'simbolFlagMenu' => [],
                'xReset' => 0,
                'teks' => '',
                'mGrpCustomer' => '',
                'mBarang' => '',
                'sat1' => '',
                'sat2' => '',
                'sat3' => '',
                'currDir' => '',
                'f1' => '',
                'f2' => '',
                'f3' => '',
                'f4' => '',
                'separator' => '',
                'myUser' => '',
                'sKodebrg' => '',
                'sKodeBrg1' => '',
                'mDevisi' => '',
                'level' => 0,
                'graphSelections' => 0,
                'xUrutPo' => 0,
                'xUrutBeli' => 0,
                'xUrutSo' => 0,
                'xUrutJual' => 0,
                'slv' => 0,
                'noUrutBeli' => 0,
                'isBrowsSimpan' => false,
                'isSO' => false,
                'levelUserAccess' => 0,
                'xN1' => 0,
                'xN2' => 0,
                'xN3' => 0,
                'xN4' => 0,
                'xList' => [],
                'letakRecord' => null,
                'gTempNoBukti' => '',
                'gTempNoBuktiSO' => '',
                'gINSGdgSPB' => '',
                'gINSBrgSPB' => '',
                'gINSGdgSO' => '',
                'gINSBrgSO' => '',
                'gINSGdgSJ' => '',
                'gINSBrgSJ' => '',
                'gINSGdgJual' => '',
                'gINSBrgJual' => '',
                'gINSGdgRJual' => '',
                'gINSBrgRJual' => '',
                'gTipeTrans' => '',
                'gTempTglLPBBeli_I' => null,
                'gDatabaseStk' => '',
                'gDatabaseGL' => '',
                'gProgram' => '',
                'gPemakaiAllGdg' => false,
                'isLRHPP' => false,
                'xIsLokal' => false,
                'Xbatal' => false,
                'SCetak' => false,
                'gIsPPN' => false,
                'gFilterKodeCustA' => '',
                'gFilterKodeCustB' => '',
                'gFilterKodeGdg' => '',
                'myTerbilang' => '',
                'xPerkPostHutPiut' => '',
                'xAktiva' => '',
                'xkodeexport' => '',
                'xCustSupp' => '',
                'gFilterTanggalA' => null,
                'gFilterTanggalB' => null,
                'gFilterKodeTipe' => 0,
                'xIsPpn' => 0,
                'xStatusBrows' => null,
                'xValue' => [],
                'jenisBJ' => '',
                'namaBJ' => '',
            ];
        });
    }

    public function boot()
    {
        // Global database connections from MyModul.dfm
        $this->app->singleton('db.stock', function () {
            return DB::connection('stock');
        });

        $this->app->singleton('db.gl', function () {
            return DB::connection('gl');
        });

        $this->app->singleton('db.transfer', function () {
            return DB::connection('transfer');
        });

        $this->app->singleton('db.marketing', function () {
            return DB::connection('marketing');
        });
    }
} 
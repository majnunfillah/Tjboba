<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DBNOMOR extends Model
{
    public $table = 'DBNOMOR';
    public $primaryKey = null;
    public $keyType = null;
    public $incrementing = false;
    public $timestamps = false;

    public const _0_ALIAS = 0;
    public const _1_KODE_TRANSAKSI = 1;
    public const _2_MMYY = 2;
    public const _3_MMYYYY = 3;
    public const _4_NOMOR_URUT = 4;
    public const _5_YYMM = 5;
    public const _6_YYYYMM = 6;

    public const _0_PEMISAH = ':';
    public const _1_PEMISAH = '-';
    public const _2_PEMISAH = '/';
    public const _3_PEMISAH = ' ';

    public const _0_RESET_BULAN = 0;
    public const _1_RESET_TAHUN = 1;


    protected $guarded = [];

    protected $hidden = ['MyID'];

    public function generateNoBukti($periode)
    {
        $nomor = $this->first();
        $PEMISAH    = '';
        $FORMAT1    = '';
        $FORMAT2    = '';
        $FORMAT3    = '';
        $FORMAT4    = '';
        $Reset      = '';

        if ($nomor->PEMISAH == 0) {
            $PEMISAH = self::_0_PEMISAH;
        } else if ($nomor->PEMISAH == 1) {
            $PEMISAH = self::_1_PEMISAH;
        } else if ($nomor->PEMISAH == 2) {
            $PEMISAH = self::_2_PEMISAH;
        } else if ($nomor->PEMISAH == 3) {
            $PEMISAH = self::_3_PEMISAH;
        }
        $date = $periode->TAHUN . $periode->BULAN . '01';
        $date = Carbon::parse($date);

        for ($i = 1; $i <= 4; $i++) {
            if ($nomor->{'FORMAT' . $i} == self::_0_ALIAS) {
                eval('$FORMAT' . $i . ' = $nomor->ALIAS;');
            } else if ($nomor->{'FORMAT' . $i} == self::_1_KODE_TRANSAKSI) {
                eval('$FORMAT' . $i . ' = "kode_transaksi";');
            } else if ($nomor->{'FORMAT' . $i} == self::_2_MMYY) {
                eval('$FORMAT' . $i . ' = $date->format("my");');
            } else if ($nomor->{'FORMAT' . $i} == self::_3_MMYYYY) {
                eval('$FORMAT' . $i . ' = $date->format("mY");');
            } else if ($nomor->{'FORMAT' . $i} == self::_4_NOMOR_URUT) {
                eval('$FORMAT' . $i . ' = "nomor_urut";');
            } else if ($nomor->{'FORMAT' . $i} == self::_5_YYMM) {
                eval('$FORMAT' . $i . ' = $date->format("ym");');
            } else if ($nomor->{'FORMAT' . $i} == self::_6_YYYYMM) {
                eval('$FORMAT' . $i . ' = $date->format("Ym");');
            }
        }

        if ($nomor->{'Reset'} == self::_0_RESET_BULAN) {
            eval('$Reset = "Bulan";');
        } else if ($nomor->{'Reset'} == self::_1_RESET_TAHUN) {
            eval('$Reset = "Tahun";');
        }

        $noBukti = $FORMAT1 . $PEMISAH . $FORMAT2 . $PEMISAH . $FORMAT3 . $PEMISAH . $FORMAT4;
        return [$noBukti, $Reset];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBMENU extends Model
{
    public $table = 'DBMENU';
    public $primaryKey = "KODEMENU";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function checkAccess()
    {
        return $this->hasOne(DBFLMENU::class, 'L1', 'KODEMENU');
    }

    public function dbflmenu()
    {
        return $this->belongsToMany(DBFLPASS::class, 'DBFLMENU', 'KODEMENU', 'USERID')
        ->withPivot('HASACCESS', 'ISTAMBAH', 'ISKOREKSI', 'ISHAPUS', 'IsBatal', 'IsOtorisasi1', 'IsOtorisasi2', 'IsOtorisasi3', 'IsOtorisasi4', 'IsOtorisasi5', 'ISCETAK', 'ISEXPORT', 'TIPE', 'pembatalan');
    }
}

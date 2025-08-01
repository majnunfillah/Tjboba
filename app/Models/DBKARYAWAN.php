<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBKARYAWAN extends Model
{
    public $table = 'dbKaryawan';
    public $primaryKey = "KeyNIK";
    public $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];

}

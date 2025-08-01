<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBJABATAN extends Model
{
    public $table = 'DBJABATAN';
    public $primaryKey = "KODEJAB";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

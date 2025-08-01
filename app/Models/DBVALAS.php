<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBVALAS extends Model
{
    public $table = 'dbVALAS';
    public $primaryKey = "KODEVLS";
    public $keyType = 'string';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];
}

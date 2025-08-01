<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBARUSKAS extends Model
{
    public $table = 'DBArusKas';
    public $primaryKey = "KodeAK";
    public $keyType = 'string';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];
}

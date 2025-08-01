<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBDEPARTEMEN extends Model
{
    public $table = 'DBDEPART';
    public $primaryKey = "KDDEP";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

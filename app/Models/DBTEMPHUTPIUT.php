<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBTEMPHUTPIUT extends Model
{
    public $table = 'DBTempHUTPIUT';
    public $primaryKey = "NoFaktur";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [
        'MyID'
    ];
}

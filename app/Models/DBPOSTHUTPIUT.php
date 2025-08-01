<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBPOSTHUTPIUT extends Model
{
    public $table = 'DBPOSTHUTPIUT';
    public $primaryKey = "NoFaktur";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBNERACA extends Model
{
    public $table = 'DBNERACA';
    public $primaryKey = 'Perkiraan';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

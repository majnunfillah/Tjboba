<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBCUSTSUPP extends Model
{
    public $table = 'DBCUSTSUPP';
    public $primaryKey = "KODECUSTSUPP";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [
        'MyID',
    ];
}

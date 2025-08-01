<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBTRANSAKSI extends Model
{
    public $table = 'dbTransaksi';
    public $primaryKey = "NoBukti";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
    
    protected $hidden = ['MyID'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBTRANS extends Model
{
    public $table = 'dbTrans';
    public $primaryKey = "NoBukti";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['MyID'];
}

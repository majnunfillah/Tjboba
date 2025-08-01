<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBMENUREPORT extends Model
{
    public $table = 'DBMENUREPORT';
    public $primaryKey = "KODEMENU";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

}

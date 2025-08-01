<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBDEVISI extends Model
{
    public $table = 'DBDEVISI';
    public $primaryKey = "DEVISI";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}

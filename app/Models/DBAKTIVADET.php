<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBAKTIVADET extends Model
{
    public $table = 'DBAKTIVADET';
    public $primaryKey = "Perkiraan";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
}

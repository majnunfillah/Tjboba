<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBSUBGROUPJNSTAMBAH extends Model
{
    public $table = 'DBSubGroupJnsTambah';
    public $primaryKey = "KodeGrp";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];
}

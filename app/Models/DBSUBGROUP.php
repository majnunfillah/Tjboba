<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBSUBGROUP extends Model
{
    public $table = 'dbSubGroup';
    public $primaryKey = "KodeSubGrp";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function sub_jenis_tambah(){
        return $this->hasMany(DBSUBGROUPJNSTAMBAH::class, 'KodeSubGrp', 'KodeSubGrp');
    }
}

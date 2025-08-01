<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBGROUP extends Model
{
    public $table = 'DBGROUP';
    public $primaryKey = "KODEGRP";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function sub_groups(){
        return $this->hasMany(DBSUBGROUP::class, 'KODEGRP', 'KodeGrp');
    }
}

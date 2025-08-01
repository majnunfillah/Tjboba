<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBBARANG extends Model
{
    public $table = 'DBBARANG';
    public $primaryKey = "KODBRG";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function spk()
    {
        return $this->hasMany(DBSPK::class, 'KODBRG', 'KODBRG');
    }
}

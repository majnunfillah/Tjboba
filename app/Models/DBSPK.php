<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DBSPK extends Model
{
    public $table = 'DBSPK';
    public $primaryKey = "NOBUKTI";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo(DBBARANG::class, 'KODBRG', 'KODBRG');
    }
}

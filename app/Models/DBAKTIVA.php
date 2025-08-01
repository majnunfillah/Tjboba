<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBAKTIVA extends Model
{
    public $table = 'DBAKTIVA';
    public $primaryKey = "Devisi";
    public $keyType = 'string';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];

    public function nomuka()
    {
        return $this->belongsTo(DBPERKIRAAN::class, 'NoMuka', 'Perkiraan');
    }

    public function akumulasi()
    {
        return $this->belongsTo(DBPERKIRAAN::class, 'Akumulasi', 'Perkiraan');
    }

    public function biaya()
    {
        return $this->belongsTo(DBPERKIRAAN::class, 'Biaya', 'Perkiraan');
    }

    public function devisi()
    {
        return $this->belongsTo(DBDEVISI::class, 'Devisi', 'Devisi');
    }
}

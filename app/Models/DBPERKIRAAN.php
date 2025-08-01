<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBPERKIRAAN extends Model
{
    public $table = 'DBPERKIRAAN';
    public $primaryKey = "Perkiraan";
    public $keyType = 'string';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['Perkiraan', 'Kelompok', 'Tipe', 'DK', 'Valas', 'KodeAK', 'KodeSAK', 'Keterangan', 'Simbol', 'FlagCashFlow', 'Neraca', 'IsPPN', 'GroupPerkiraan', 'Lokasi'];
    // protected $guarded = [];

    protected $hidden = ['MyID'];

    
    public function akses_perkiraan()
    {
        return $this->belongsToMany(DBFLPASS::class, 'DBAKSESPERKIRAAN', 'Perkiraan', 'UserID');
    }

    public function valas()
    {
        return $this->belongsTo(DBVALAS::class, 'Valas', 'KODEVLS');
    }

    public function aruskas()
    {
        return $this->belongsTo(DBARUSKAS::class, 'KodeAK', 'KodeAK');
    }

    public function aruskasdet()
    {
        return $this->belongsTo(DBARUSKASDET::class, 'KodeSAK', 'KodeSubAK');
    }

    public function kelompok_kas(){
        return $this->hasOne(DBPOSTHUTPIUT::class, 'Perkiraan', 'Perkiraan');
    }
}

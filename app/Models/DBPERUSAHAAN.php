<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DBPERUSAHAAN extends Model
{
    public $table = 'DBPERUSAHAAN';
    public $primaryKey = "KODEUSAHA";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['KODEUSAHA', 'TTD', 'LOGO'];

    public function getTTDPATHAttribute()
    {
        return ($this->attributes['TTD_PATH'] == NULL || $this->attributes['TTD_PATH'] == '') ? NULL : asset(Storage::url($this->attributes['TTD_PATH']));
    }

    public function getLOGOPATHAttribute()
    {
        return ($this->attributes['LOGO_PATH'] == NULL || $this->attributes['LOGO_PATH'] == '') ? NULL : asset(Storage::url($this->attributes['LOGO_PATH']));
    }
}

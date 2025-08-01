<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBARUSKASDET extends Model
{
    public $table = 'DBArusKasDet';
    public $primaryKey = "KodeSubAK";
    public $keyType = 'string';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];
}

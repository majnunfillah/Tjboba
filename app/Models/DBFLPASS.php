<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class DBFLPASS extends Authenticatable
{
    public $table = 'DBFLPASS';
    public $primaryKey = "USERID";
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['UID', 'UID2'];

    public function getAuthPassword()
    {
        return $this->UID;
    }

    public function getAuthIdentifier()
    {
        return $this->USERID;
    }

    public function jabatan()
    {
        return $this->hasOne(DBJABATAN::class, 'KODEJAB', 'KodeJab');
    }

    public function departemen()
    {
        return $this->hasOne(DBDEPARTEMEN::class, 'KDDEP', 'kodeBag');
    }

    public function karyawan()
    {
        return $this->hasOne(DBKARYAWAN::class, 'keyNIK', 'keynik');
    }

    public function dbflmenu()
    {
        return $this->belongsToMany(DBMENU::class, 'DBFLMENU', 'USERID', 'L1')
            ->withPivot('HASACCESS', 'ISTAMBAH', 'ISKOREKSI', 'ISHAPUS', 'IsBatal', 'IsOtorisasi1', 'IsOtorisasi2', 'IsOtorisasi3', 'IsOtorisasi4', 'IsOtorisasi5', 'ISCETAK', 'ISEXPORT', 'TIPE', 'pembatalan');
    }

    public function perkiraan()
    {
        return $this->belongsToMany(DBPERKIRAAN::class, 'DBAKSESPERKIRAAN', 'UserID', 'Perkiraan');
    }


    /**
     * @param srting $codeAccess
     * @param string $codeMenu
     * @return bool
     */
    public function canAccess(string $codeAccess, string $codeMenu)
    {
        return $this->join('DBFLMENU as key', 'key.USERID', '=', 'DBFLPASS.USERID')
            ->where('key.USERID', $this->USERID)
            ->where('key.L1', $codeMenu)
            ->where("key.$codeAccess", 1)
            ->get();
    }

    /**
     * @param string $codeMenu
     * @return array
     */
    public function getPermissionsName(string $codeMenu)
    {
        $keys = [
            "HASACCESS",
            "ISTAMBAH",
            "ISKOREKSI",
            "ISHAPUS",
            "ISCETAK",
            "ISEXPORT",
            "IsOtorisasi1",
            "IsOtorisasi2",
            "IsOtorisasi3",
            "IsOtorisasi4",
            "IsOtorisasi5",
            "IsBatal"
        ];
        if ($access = $this->join('DBFLMENU as key', 'key.USERID', '=', 'DBFLPASS.USERID')
            ->where('key.USERID', $this->USERID)
            ->where('key.L1', $codeMenu)
            ->first()
            ->toArray()
        ) {
            return array_filter($keys, function ($key) use ($access) {
                foreach ($access as $i => $value) {
                    if ($i === $key && $value == 1) {
                        return $value;
                    }
                }
            });
        }
        return [];
    }
}

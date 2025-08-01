<?php

namespace App\Http\Repository;

use App\Http\Repository\Task\DbMenuInterface;
use Illuminate\Support\Facades\Storage;

class DbMenuRepository extends BaseRepository implements DbMenuInterface
{
    public function __construct()
    {
        parent::__construct(['DbMenu', 'KODEMENU']);
    }
}

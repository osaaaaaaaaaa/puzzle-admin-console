<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attached_Item extends Model
{
    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];
}

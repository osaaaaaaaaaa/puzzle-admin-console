<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory_Item extends Model
{
    use HasFactory;

    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];
}
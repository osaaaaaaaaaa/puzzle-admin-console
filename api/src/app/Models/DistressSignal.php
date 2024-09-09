<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistressSignal extends Model
{
    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];

    // 参加ゲストを取得するリレーション
    public function guests()
    {
        return $this->hasMany(Guest::class, 'distress_signal_id', 'id');
    }
}

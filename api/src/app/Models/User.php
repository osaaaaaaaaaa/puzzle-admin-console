<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];

    // 所持アイテムのリレーション
    public function items()
    {
        // 中間テーブルに関する複数行を取得
        return $this->belongsToMany(
        // 第二モデル , 第三テーブル , 第一モデルと関係のあるカラム , 第二モデルと関係のあるカラム
            Item::class, 'inventory__items', 'user_id', 'item_id')
            ->withPivot('amount');  // 中間テーブルのカラムを取得
    }

    // 受信メールのリレーション
    public function mails()
    {
        return $this->hasMany(Received_Mail::class);
    }

    // フォローのリレーション
    public function follows()
    {
        return $this->hasMany(Follow::class);
    }
}

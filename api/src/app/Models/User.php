<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];

    // アチーブメントの称号を取得するリレーション
    public function achievements()
    {
        return $this->hasMany(Achievement::class, 'id', 'achievement_id');
    }

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
        return $this->hasMany(UserMail::class);
    }

    // フォローのリレーション
    public function follows()
    {
        return $this->belongsToMany(
            User::class, 'following_users', 'user_id', 'following_user_id')
            ->withPivot('id');
    }

    // フォローログのリレーション
    public function follow_logs()
    {
        return $this->belongsToMany(
            User::class, 'follow_logs', 'user_id', 'target_user_id')
            ->withPivot('id', 'action', 'created_at');
    }

    // アイテムログのリレーション
    public function item_logs()
    {
        return $this->belongsToMany(
            Item::class, 'item_logs', 'user_id', 'item_id')
            ->withPivot('id', 'option_id', 'allie_count', 'created_at');
    }

    // メールログのリレーション
    public function mail_logs()
    {
        return $this->hasMany(MailLogs::class);
    }
}

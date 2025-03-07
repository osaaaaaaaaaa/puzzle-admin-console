<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasApiTokens;

    // $guardedには更新しないカラムを指定する
    protected $guarded = [
        'id',
    ];

    // 相互フォローのユーザーを取得する
    public function agreement()
    {
        $follow_users = FollowingUser::where('user_id', '=', $this->id)->get();
        return User::selectRaw('users.id AS id,name,stage_id,icon_id')
            ->join('following_users', 'following_users.user_id', '=', 'users.id')
            ->whereIn('user_id', $follow_users->pluck('following_user_id'))
            ->where('following_user_id', '=', $this->id)->get();
    }

    // ステージリザルトのリレーション
    public function stageresult()
    {
        return $this->hasMany(StageResult::class, 'user_id', 'id');
    }

    // 合計ポイントを取得するリレーション
    public function totalpoint()
    {
        return $this->hasMany(UserItem::class, 'user_id', 'id');
    }

    // 合計スコアを取得するリレーション
    public function totalscore()
    {
        return $this->hasMany(StageResult::class, 'user_id', 'id')
            ->selectRaw('SUM(score) AS total_score')->groupBy('user_id');
    }

    // 称号を取得するリレーション
    public function gettitle()
    {
        return $this->hasMany(Item::class, 'id', 'title_id');
    }

    // 所持アイテムのリレーション
    public function items()
    {
        // 中間テーブルに関する複数行を取得
        return $this->belongsToMany(
        // 第二モデル , 第三テーブル , 第一モデルと関係のあるカラム , 第二モデルと関係のあるカラム
            Item::class, 'user_items', 'user_id', 'item_id')
            ->withPivot('amount');  // 中間テーブルのカラムを取得
    }

    // 受信メールのリレーション
    public function mails()
    {
        // 中間テーブルに関する複数行を取得
        return $this->belongsToMany(
        // 第二モデル , 第三テーブル , 第一モデルと関係のあるカラム , 第二モデルと関係のあるカラム
            Mail::class, 'user_mails', 'user_id', 'mail_id')
            ->withPivot('is_received');
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

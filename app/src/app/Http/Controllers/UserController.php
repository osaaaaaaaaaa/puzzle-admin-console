<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\FollowingUser;
use App\Models\Level;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Http\Request;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;

class UserController
{
    // ユーザー一覧表示
    public function index(Request $request)
    {
        // モデルを取得する
        $user = User::find($request->id);

        // リレーション
        if (!empty($user)) {
            // ユーザーのレベルを取得
            $level = Level::where('exp', '<=', $user->exp)
                ->orderBy('level', 'desc')->first();

            // 設定しているアチーブメントのタイトルを取得する
            $achievement = $user->index()->selectRaw('title')->first();

            // 最大レベルを超えている場合
            if (empty($level)) {
                $level = Level::max('level');
            }

            if (empty($achievement)) {
                $achievement = '';
            }
        }

        return view('users/index',
            ['user' => $user ?? null, 'level' => $level ?? null, 'achievement' => $achievement ?? null]);
    }

    // インベントリのアイテム一覧表示
    public function item(Request $request)
    {
        // モデルを取得する
        $user = User::find($request->id);

        // モデルを取得できた場合
        if (!empty($user)) {
            $items = $user->items()->paginate(10);
            $items->appends(['id' => $request->id]);    // ページネーションで遷移したときにパラメータが消えないようにする
        }

        return view('users/item', ['user' => $user, 'items' => $items ?? null]);
    }

    // 受信メール一覧表示
    public function mail(Request $request)
    {
        // モデルを取得する
        $user = User::find($request->id);

        // リレーション
        if (!empty($user)) {
            $mails = $user->mails()->paginate(10);
            $mails->appends(['id' => $request->id]);
        }

        return view('users/mail', ['user' => $user, 'mails' => $mails ?? null]);
    }

    // フォロー一覧表示
    public function follow(Request $request)
    {
        // モデルを取得する
        $user = User::find($request->id);

        if (!empty($user)) {
            // リレーション
            $following_users = $user->follows()->paginate(10);
            $following_users->appends(['id' => $request->id]);

            // 相互フォローかどうかの情報を格納する
            for ($i = 0; $i < count($following_users); $i++) {
                $isFollow = FollowingUser::where('user_id', '=', $following_users[$i]->id)
                    ->where('following_user_id', '=', $user->id)->exists();
                $following_users[$i]['is_agreement'] = $isFollow === true ? 1 : 0;
            }
        }

        return view('users/follow', ['user' => $user ?? null, 'following_users' => $following_users ?? null]);
    }

    // アチーブメントの達成状況
    public function achievement(Request $request)
    {
        // モデルを取得する
        $achievements = UserAchievement::where('user_id', '=', $request->id)->paginate(10);
        $user = User::find($request->id);

        return view('users/achievement', ['user' => $user ?? null, 'achievements' => $achievements ?? null]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FollowingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\Debugbar\Facades\Debugbar;

class UserController
{
    // ユーザー一覧表示
    public function index(Request $request)
    {
        // アカウント名の指定があるかどうか
        if (empty($request->id)) {// 指定がない場合
            // アカウントテーブルから全てのレコードを取得する
            $users = User::paginate(20);
            return view('users/index', ['users' => $users]);
        } else {// 指定がある場合
            // 条件指定してレコードを取得する
            $users = User::where('id', '=', $request->id)->paginate(20);
            return view('users/index', ['users' => $users, 'name' => $request->name]);
        }
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
}

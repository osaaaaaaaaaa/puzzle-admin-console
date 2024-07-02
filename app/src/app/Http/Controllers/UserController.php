<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Received_Mail;
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
    public function mail()
    {
        $mails = Received_Mail::selectRaw('received__mails.id AS id,mail_id,users.name AS name,is_received,
        received__mails.created_at AS created_at,received__mails.updated_at AS updated_at')
            ->join('users', 'received__mails.user_id', '=', 'users.id')
            ->paginate(10);

        return view('users/mail', ['mails' => $mails]);
    }

    // フォロー一覧表示
    public function follow(Request $request)
    {
        if (empty($request->id)) {
            $users = Follow::selectRaw('follows.id AS id , u1.name AS user_name,u2.name AS following_name,
        is_agreement,follows.created_at AS created_at,follows.updated_at AS updated_at')
                ->join('users AS u1', 'follows.user_id', '=', 'u1.id')
                ->join('users AS u2', 'follows.following_id', '=', 'u2.id')
                ->get();
        } else {
            $users = Follow::selectRaw('follows.id AS id , u1.name AS user_name,u2.name AS following_name,
        is_agreement,follows.created_at AS created_at,follows.updated_at AS updated_at')
                ->join('users AS u1', 'follows.user_id', '=', 'u1.id')
                ->join('users AS u2', 'follows.following_id', '=', 'u2.id')
                ->where('follows.user_id', '=', $request->id)
                ->get();
        }

        return view('users/follow', ['users' => $users ?? null]);
    }
}

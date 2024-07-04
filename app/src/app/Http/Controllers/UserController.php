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
    public function mail(Request $request)
    {
        if (!empty($request->id)) {
            $mails = Received_Mail::selectRaw('received__mails.id AS id,mail_id,users.name AS name,is_received,
        received__mails.created_at AS created_at,received__mails.updated_at AS updated_at')
                ->join('users', 'received__mails.user_id', '=', 'users.id')
                ->where('received__mails.user_id', '=', $request->id)
                ->paginate(10);
        } else {
            $mails = Received_Mail::selectRaw('received__mails.id AS id,mail_id,users.name AS name,is_received,
        received__mails.created_at AS created_at,received__mails.updated_at AS updated_at')
                ->join('users', 'received__mails.user_id', '=', 'users.id')
                ->paginate(10);
        }

        return view('users/mail', ['mails' => $mails]);
    }

    // フォロー一覧表示
    public function follow(Request $request)
    {
        if (empty($request->id)) {
            $users = Follow::selectRaw('following_users.id AS id,following_users.user_id , following_users.following_user_id , u1.name AS user_name,u2.name AS following_name,
            following_users.created_at AS created_at,following_users.updated_at AS updated_at')
                ->join('users AS u1', 'following_users.user_id', '=', 'u1.id')
                ->join('users AS u2', 'following_users.following_user_id', '=', 'u2.id')
                ->get();
        } else {
            $users = Follow::selectRaw('following_users.id AS id ,following_users.user_id , following_users.following_user_id , u1.name AS user_name,u2.name AS following_name,
            following_users.created_at AS created_at,following_users.updated_at AS updated_at')
                ->join('users AS u1', 'following_users.user_id', '=', 'u1.id')
                ->join('users AS u2', 'following_users.following_user_id', '=', 'u2.id')
                ->where('following_users.user_id', '=', $request->id)
                ->get();
        }

        // 相互フォローかどうかの情報を格納する
        $is_agreement = [];
        for ($i = 0; $i < count($users); $i++) {
            $following_user = Follow::where('user_id', '=', $users[$i]['following_user_id'])
                ->where('following_user_id', '=', $users[$i]['user_id'])->exists();
            $is_agreement[$i] = $following_user === true ? 1 : 0;
        }

        return view('users/follow', ['users' => $users ?? null, 'is_agreement' => $is_agreement ?? null]);
    }
}

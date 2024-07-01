<?php

namespace App\Http\Controllers;

use App\Models\Received_Mail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    // 受信メール一覧表示
    public function mail()
    {
        $mails = Received_Mail::selectRaw('received__mails.id AS id,mail_id,user_name AS name,is_received,
        received__mails.created_at AS created_at,received__mails.updated_at AS updated_at')
            ->join('users', 'received__mails.user_id', '=', 'users.id')
            ->paginate(10);

        return view('users/mail', ['mails' => $mails]);
    }
}

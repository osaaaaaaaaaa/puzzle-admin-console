<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController
{
    // プレイヤー一覧表示
    public function index(Request $request)
    {
        // 'login' セッションがあるかどうか
        if ($request->session()->has('login')) {
            // アカウント名の指定があるかどうか
            if (empty($request->player_name)) {// 指定がない場合
                // アカウントテーブルから全てのレコードを取得する
                $players = Player::All();
                return view('players/index', ['players' => $players]);
            } else {// 指定がある場合
                // 条件指定してレコードを取得する
                $players = Player::where('player_name', '=', $request->player_name)->get();
                return view('players/index', ['players' => $players]);
            }
        } else {
            return redirect('/');
        }
    }
}

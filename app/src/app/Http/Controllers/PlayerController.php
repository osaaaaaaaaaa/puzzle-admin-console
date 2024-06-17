<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlayerController
{
    // プレイヤー一覧表示
    public function index(Request $request)
    {
        if ($request->session()->has('login')) {
            if (empty($request->player_name)) {
                return view('players/index', ['dataList' => $this->getPlayerData()]);
            } else {
                dd($request->player_name);
            }
        } else {
            return redirect('/');
        }
    }

    // データ管理
    public function getPlayerData()
    {
        return [
            [
                'id' => 1,
                'player_name' => 'test01',
                'level' => (int)rand(1, 100),
                'exp' => (int)rand(1, 10000),
                'life' => (int)rand(1, 10)
            ],
            [
                'id' => 2,
                'player_name' => 'test02',
                'level' => (int)rand(1, 100),
                'exp' => (int)rand(1, 10000),
                'life' => (int)rand(1, 10)
            ],
            [
                'id' => 3,
                'player_name' => 'test03',
                'level' => (int)rand(1, 100),
                'exp' => (int)rand(1, 10000),
                'life' => (int)rand(1, 10)
            ],
        ];
    }

}

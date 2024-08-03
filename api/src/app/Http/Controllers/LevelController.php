<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    // レベル一覧表示
    public function index(Request $request)
    {
        // レコードを取得してviewを表示する
        $levels = Level::paginate(20);
        return view('levels/index', ['levels' => $levels]);
    }
}

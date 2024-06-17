<?php

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public $userDatas = [['id' => 1, 'name' => 'jobi', 'password' => 'jobi']];

    // ログイン画面を表示する
    public function showLoginPage(Request $request)
    {
        // 既にログインしている場合
        if ($request->session()->has('login')) {
            return redirect('home/index');
        }

        return view('accounts/login');
    }

    // ログイン処理
    public function doLogin(Request $request)
    {
        if ($request['name'] == 'jobi' && $request['password'] == 'jobi') {
            // セッションに指定のキーで値を保存する
            $request->session()->put('login', true);

            return redirect('home/index');
        } else {
            return redirect('/')->with(['errors' => 'パスワード、ユーザー名が異なる']);
        }
    }

    // ログアウト処理
    public function doLogout(Request $request)
    {
        // セッションから全てのキーの値を削除する
        $request->session()->flush();

        return redirect('/');
    }

    // アカウント一覧
    public function index(Request $request)
    {
        if ($request->session()->has('login')) {
            if (empty($request->account_id)) {
                return view('accounts/index', ['title' => '■ アカウント一覧', 'dataList' => $this->userDatas]);
            } else {
                dd($request->account_id);
            }
        } else {
            return redirect('/');
        }

    }

    // ホームページ表示
    public function showHomePage(Request $request)
    {
        if ($request->session()->has('login')) {
            return view('home/index');
        } else {
            return redirect('/');
        }
    }
}

//        // セッションに指定のキーで値を保存する
//        $request->session()->put('login', true);
//
//        // セッションから指定のキーの値を取得
//        Debugbar::info($request->session()->pull('login'));
//
//        // セッションから削除
//        $request->session()->forget('login');
//
//        // セッションから全てのキーの値を削除する
//        $request->session()->flush();
//
//        // セッションに指定したキーが存在するかどうか
//        if ($request->session()->exists('login')) {
//            Debugbar::info($request->session()->exists('login'));
//        }

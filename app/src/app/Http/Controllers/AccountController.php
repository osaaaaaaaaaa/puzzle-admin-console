<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller
{
    // アカウント一覧
    public function index(Request $request)
    {
        // 'login' セッションがあるかどうか
        if ($request->session()->has('login')) {
            // アカウント名の指定があるかどうか
            if (empty($request->account_name)) {// 指定がない場合
                // アカウントテーブルから全てのレコードを取得する
                $accounts = Account::All();
                return view('accounts/index', ['accounts' => $accounts]);
            } else {// 指定がある場合
                // 条件指定してレコードを取得する
                $accounts = Account::where('name', '=', $request->account_name)->get();
                return view('accounts/index', ['accounts' => $accounts]);
            }
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

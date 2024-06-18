<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ログイン画面を表示する
    public function showLoginPage(Request $request)
    {
        // 既にログインしている場合
        if ($request->session()->has('login')) {
            return redirect('home/index');
        }

        return view('auths/index', ['error' => $request['error'] ?? null]);
    }

    // ログイン処理
    public function doLogin(Request $request)
    {
        route('login');

//        // バリデーションチェック
//        $validated = $request->validate([
//            'name' => ['required', 'min:4', 'max:20'],
//            'password' => ['required'],
//        ]); // エラー発生で自動で元のページへ戻る

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'min:4'],
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect("/")
                ->withErrors($validator)
                ->withInput();
        }

        // レコードを取得
        $account = Account::where('name', '=', $request['name'])->get();
        if ($account->count() > 0) {// レコードを取得できた場合
            // ハッシュ化したkeyと一致するかどうか
            if (Hash::check($request['password'], $account[0]->password)) {
                // セッションに指定のキーで値を保存する
                $request->session()->put('login', true);
                return redirect('home/index');
            }
        }
        // ルートに名前を指定
        return redirect()->route('login', ['error' => 'invalid']);
    }

    // ログアウト処理
    public function doLogout(Request $request)
    {
        // セッションから全てのキーの値を削除する
        $request->session()->flush();

        return redirect('/');
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

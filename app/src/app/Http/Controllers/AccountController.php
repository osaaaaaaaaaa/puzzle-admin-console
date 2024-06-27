<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Barryvdh\Debugbar\Facades\Debugbar;

class AccountController extends Controller
{
    // アカウント一覧
    public function index(Request $request)
    {
        // アカウント名の指定があるかどうか
        if (empty($request->id)) {// 指定がない場合
            // アカウントテーブルから全てのレコードを取得する
            $accounts = Account::All();
            return view('accounts/index',
                ['accounts' => $accounts, 'normally' => $request['normally']]);
        } else {// 指定がある場合
            // 条件指定してレコードを取得する
            $accounts = Account::where('id', '=', $request->id)->get();
            return view('accounts/index',
                ['accounts' => $accounts, 'normally' => $request['normally']]);
        }
    }

    // アカウント登録フォーム表示処理
    public function create(Request $request)
    {
        return view('accounts/create', [
            'error' => $request['error'] ? '入力した名前は既に存在します' : null,
            'result' => $request['create_account'] ? $request['create_account'] . 'を登録しました' : null
        ]);
    }

    // アカウント登録処理
    public function store(Request $request)
    {
        // カスタムバリデーション
        $validator = Validator::make($request->all(), [
            'account_name' => ['required', 'min:4', 'max:20'],
            'password' => ['required', 'min:4', 'max:20', 'confirmed']  // パスワードが一致しているかどうか
        ]);

        if ($validator->fails()) {
            return redirect()->route('accounts.create')
                ->withErrors($validator)
                ->withInput();
        }

        // 入力された名前がレコードに存在しない場合
        $isAccountExist = Account::where('name', '=', $request->account_name)->exists();
        Debugbar::info($isAccountExist);
        if (!$isAccountExist) {
            // レコードを追加する
            Account::create(['name' => $request->account_name, 'password' => Hash::make($request->password1)]);
            return redirect()->route('accounts.create',
                ['create_account' => $request->account_name]);  // ルートに名前を指定
        }

        return redirect()->route('accounts.create', ['error' => 'invalid']);  // ルートに名前を指定
    }

    // アカウント削除処理
    public function destroy(Request $request)
    {
        $account = Account::findorFail($request['destroy_account_id']);
        $account->delete();

        return redirect()->route('accounts.index');
    }

    // パスワード更新処理
    public function update(Request $request)
    {
        // カスタムバリデーション
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'min:4', 'max:20', 'confirmed']  // パスワードが一致しているかどうか
        ]);

        if ($validator->fails()) {
            return redirect()->route('accounts.index')
                ->withErrors($validator)
                ->withInput();
        }

        // パスワード更新
        $account = Account::findorFail($request['update_account_id']);
        $account['password'] = Hash::make($request['password']);
        $account->save();

        return redirect()->route('accounts.index', ['normally' => 'valid']);
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

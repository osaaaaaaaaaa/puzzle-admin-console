<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\NoCacheMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ミドルウェアのルートを通す(キャッシュを保存しない)
Route::middleware([NoCacheMiddleware::class])->group(function () {

    // [ 認証 ] ##########################################################################

    // ログインページ表示
    Route::get('/', [AuthController::class, 'index'])->name('auths.index');

    // ログイン処理
    Route::post('auths/dologin', [AuthController::class, 'doLogin'])->name('auths.dologin');

    // ログアウト処理
    Route::get('auths/dologout', [AuthController::class, 'doLogout'])->name('auths.dologout');

    Route::prefix('')->middleware([AuthMiddleware::class])
        ->get('home/index', [AuthController::class, 'showHomePage'])->name('home.index');


    // [ アカウント ] ##########################################################################

    Route::prefix('accounts')->name('accounts.')->controller(AccountController::class)
        ->middleware(AuthMiddleware::class) // 認証処理 (※ログインした後の処理のみをグループ化すること！ ... ログイン処理とかにやるとおかしくなる)
        ->group(function () {
            Route::get('/', 'index')->name('index');            // 一覧表示(accounts.index)
            Route::get('create', 'create')->name('create');     // 登録画面表示(accounts.create)
        });

    // アカウント一覧表示
    Route::get('accounts/index/{id?}', [AccountController::class, 'index'])->name('accounts.show');
    // アカウント登録処理
    Route::post('accounts/store', [AccountController::class, 'store'])->name('accounts.store');
    // アカウント削除処理
    Route::post('accounts/destroy', [AccountController::class, 'destroy'])->name('accounts.destroy');
    // アカウント更新処理
    Route::post('accounts/update', [AccountController::class, 'update'])->name('accounts.update');


    // [ マスタデータ ] ##########################################################################

    Route::prefix('')->middleware([AuthMiddleware::class])->group(function () {
        Route::get('items/index', [ItemController::class, 'items_index'])->name('items.index');   // アイテム一覧表示
        Route::get('mails/index', [MailController::class, 'index'])->name('mails.index');         // メール一覧表示
        Route::get('mails/create', [MailController::class, 'create'])->name('mails.create');      // メール送信画面表示
    });

    // [ ユーザーデータ ] ##########################################################################

    Route::prefix('users')->name('users.')->controller(UserController::class)
        ->middleware([AuthMiddleware::class])->group(function () {
            // プレイヤー一覧表示
            Route::get('/', 'index')->name('index');
            // 受信メール一覧表示
            Route::get('mail', 'mail')->name('mail');
            // フォロー一覧表示
            Route::get('follow', 'follow')->name('follow');
            // インベントリアイテム一覧表示
            Route::get('item', 'item')->name('item');
        });

    // プレイヤー一覧表示(検索用)
    Route::get('users/index/{id?}', [UserController::class, 'index'])->name('users.index.show');

    // インベントリアイテム一覧表示(検索用)
    Route::get('users/item/{id?}', [UserController::class, 'item'])->name('users.item.show');

    // フォロー一覧表示(検索用)
    Route::get('users/follow/{id?}', [UserController::class, 'follow'])->name('users.follow.show');

    // メール作成・送信処理
    Route::post('mails/store', [MailController::class, 'store'])->name('mails.store');

    // 受信メール一覧表示(検索用)
    Route::get('users/mail/{id?}', [UserController::class, 'mail'])->name('users.mail.show');
});

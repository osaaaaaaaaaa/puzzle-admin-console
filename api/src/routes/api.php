<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\NoCacheMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ミドルウェアのルートを通す(キャッシュを保存しない)
Route::middleware([NoCacheMiddleware::class])->group(function () {

    // [ ユーザー ] #####################################################################################################
    Route::prefix('users')->name('users.')->controller(UserController::class)
        ->group(function () {
            // 所持アイテムリスト
            Route::get('/items/{user_id}', 'item')->name('items');              // ID指定
            Route::get('/mails/{user_id}', 'mail')->name('mails');              // ID指定
            Route::get('/follows/{user_id}', 'follow')->name('follows');        // ID指定

            // ユーザー情報
            Route::get('/index', 'index')->name('index');       // レベル指定
            Route::get('/{user_id}', 'show')->name('show');     // ID指定

        });

    // [ マスタデータ ] ##################################################################################################
    Route::get('mails', [MailController::class, 'index'])->name('mails.index');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
});

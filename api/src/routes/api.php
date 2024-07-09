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
            // ユーザー情報表示・登録・更新
            Route::get('/index', 'index')->name('index');
            Route::get('/{user_id}', 'show')->name('show');
            Route::post('store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');

            // フォローリスト表示・登録・解除
            Route::get('/follow/show', 'showFollow')->name('follow.show');
            Route::post('/follow/store', 'storeFollow')->name('follow.store');
            Route::post('/follow/destroy', 'destroyFollow')->name('follow.destroy');

            // 所持アイテム表示・更新
            Route::get('/item/show', 'showItem')->name('item.show');
            Route::post('/item/update', 'updateItem')->name('item.update');

            // メールリスト表示・開封
            Route::get('/mail/show', 'showMail')->name('mail.show');
            Route::post('/mail/update', 'updateMail')->name('mail.update');
        });

    // [ マスタデータ ] ##################################################################################################
    Route::get('mail', [MailController::class, 'index'])->name('mails.index');
    Route::get('item', [ItemController::class, 'index'])->name('items.index');
});

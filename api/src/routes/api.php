<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\DistressSignalController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LogsController;
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
            // ユーザー情報取得・登録・更新
            Route::get('/show', 'show')->name('show');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');

            // フォローリスト取得・登録・解除
            Route::get('/follow/show', 'showFollow')->name('follow.show');
            Route::post('/follow/store', 'storeFollow')->name('follow.store');
            Route::post('/follow/destroy', 'destroyFollow')->name('follow.destroy');

            // おすすめのユーザーリスト取得
            Route::get('/recommended/show', 'showRecommendedUser')->name('recommended.show');

            // 所持アイテムリスト取得・更新
            Route::get('/item/show', 'showItem')->name('item.show');
            Route::post('/item/update', 'updateItem')->name('item.update');

            // メールリスト取得・開封
            Route::get('/mail/show', 'showMail')->name('mail.show');
            Route::post('/mail/update', 'updateMail')->name('mail.update');

            // ステージリザルト取得・更新
            Route::get('/stage/result/show', 'showStageResult')->name('stage.result.show');
            Route::post('/stage/clear/update', 'updateStageClear')->name('stage.clear.update');

            // ランキング取得・フォロー内でのランキング取得
            Route::get('/ranking/show', 'showRanking')->name('ranking.show');
            Route::get('/follow/ranking/show', 'showFollowRanking')->name('follow.ranking.index');
        });

    // アチーブメントの達成状況更新
    Route::post('users/achievements/update', [AchievementController::class, 'update'])->name('achievements.update');

    // [ 救難信号 ] #####################################################################################################

    Route::prefix('distress_signals')->name('distress_signals.')->controller(DistressSignalController::class)
        ->group(function () {
            //************************
            // ホスト関連のAPI
            //************************
            // 発信中の救難信号を取得
            Route::get('/index', 'index')->name('index');
            // 救難信号をランダムに取得
            Route::get('/show', 'show')->name('show');
            // 救難信号の登録
            Route::post('/store', 'store')->name('store');
            // 救難信号の更新
            Route::post('/update', 'update')->name('update');
            // 救難信号削除
            Route::post('/destroy', 'destroy')->name('destroy');

            //************************
            // ゲスト関連のAPI
            //************************
            // ゲスト取得
            Route::get('/guest/show', 'showGuest')->name('guest.show');
            // ゲスト更新(参加・配置情報更新)
            Route::post('/guest/update', 'updateGuest')->name('guest.update');
            // 救難信号の報酬受け取り
            Route::post('/reward/update', 'claimReward')->name('reward.update');

            //************************
            // リプレイ情報関連のAPI
            //************************
            // リプレイ情報取得
            Route::get('/replay/show', 'showReplay')->name('replay.show');
            // リプレイ情報登録
            Route::post('/replay/store', 'storeReplay')->name('replay.store');

            //******************************************
            // 救難信号の募集[ホスト]・参加ログ[ゲスト]を取得
            //******************************************
            // 募集ログを取得
            Route::get('/host_log', 'indexHostLog')->name('log.host');

            // 参加ログを取得
            Route::get('/guest_log', 'indexGuestLog')->name('log.guest');
        });

    // [ ログ ] #########################################################################################################
    Route::get('logs/follow', [LogsController::class, 'follow'])->name('logs.follow');

    // [ マスタデータ ] ##################################################################################################

    // メール取得
    Route::get('mail', [MailController::class, 'index'])->name('mails.index');
    // アイテム取得
    Route::get('item', [ItemController::class, 'index'])->name('items.index');
    // アチーブメント取得
    Route::get('achievements', [AchievementController::class, 'index'])->name('achievements.index');
});

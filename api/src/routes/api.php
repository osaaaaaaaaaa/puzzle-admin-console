<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ConstantController;
use App\Http\Controllers\DistressSignalController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\NoCacheMiddleware;
use App\Models\Constant;
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
            Route::post('/update', 'update')->middleware('auth:sanctum')->name('update');

            // フォローリスト取得・登録・解除
            Route::get('/follow/show', 'showFollow')->name('follow.show');
            Route::post('/follow/store', 'storeFollow')
                ->middleware('auth:sanctum')->name('follow.store');
            Route::post('/follow/destroy', 'destroyFollow')
                ->middleware('auth:sanctum')->name('follow.destroy');

            // おすすめのユーザーリスト取得
            Route::get('/recommended/show', 'showRecommendedUser')->name('recommended.show');

            // 所持アイテムリスト取得・更新
            Route::get('/item/show', 'showItem')->name('item.show');
            Route::post('/item/update', 'updateItem')
                ->middleware('auth:sanctum')->name('item.update');

            // メールリスト取得・開封・削除
            Route::get('/mail/show', 'showMail')->name('mail.show');
            Route::post('/mail/update', 'updateMail')
                ->middleware('auth:sanctum')->name('mail.update');
            Route::post('/mail/destroy', 'destroyMail')
                ->middleware('auth:sanctum')->name('mail.destroy');

            // ステージリザルト取得・更新
            Route::get('/stage/result/show', 'showStageResult')
                ->middleware('auth:sanctum')->name('stage.result.show');
            Route::post('/stage/clear/update', 'updateStageClear')
                ->middleware('auth:sanctum')->name('stage.clear.update');

            // ランキング取得・フォロー内でのランキング取得
            Route::get('/ranking/show', 'showRanking')->name('ranking.show');
            Route::get('/follow/ranking/show', 'showFollowRanking')->name('follow.ranking.index');

            // アクセストークン生成処理
            Route::post('/token/store', 'createToken')->name('token.store');
        });

    // アチーブメントの達成状況更新
    Route::post('users/achievements/update', [AchievementController::class, 'update'])
        ->middleware('auth:sanctum')->name('achievements.update');
    // アチーブメント報酬受け取り
    Route::post('users/achievements/receive', [AchievementController::class, 'receive'])
        ->middleware('auth:sanctum')->name('achievements.receive');

    // [ 救難信号 ] #####################################################################################################

    Route::prefix('distress_signals')->name('distress_signals.')->controller(DistressSignalController::class)
        ->group(function () {
            // 救難信号に参加しているユーザーのプロフィール取得
            Route::get('/user/show', 'showUser')->name('user.show');

            //************************
            // ホスト関連のAPI
            //************************
            // 発信中の救難信号を取得
            Route::get('/index', 'index')->name('index');
            // 救難信号をランダムに取得
            Route::get('/show', 'show')->name('show');
            // 救難信号の登録
            Route::post('/store', 'store')
                ->middleware('auth:sanctum')->name('store');
            // 救難信号の更新
            Route::post('/update', 'update')
                ->middleware('auth:sanctum')->name('update');
            // 救難信号削除
            Route::post('/destroy', 'destroy')
                ->middleware('auth:sanctum')->name('destroy');

            //************************
            // ゲスト関連のAPI
            //************************
            // ゲスト取得
            Route::get('/guest/show', 'showGuest')->name('guest.show');
            // ゲスト更新(参加・配置情報更新)
            Route::post('/guest/update', 'updateGuest')
                ->middleware('auth:sanctum')->name('guest.update');
            // ゲスト削除
            Route::post('/guest/destroy', 'destroyGuest')
                ->middleware('auth:sanctum')->name('guest.destroy');
            // 救難信号の報酬受け取り
            Route::post('/reward/update', 'claimReward')
                ->middleware('auth:sanctum')->name('reward.update');

            //************************
            // リプレイ情報関連のAPI
            //************************
            // リプレイ情報取得
            Route::get('/replay/show', 'showReplay')->name('replay.show');
            // リプレイ情報登録
            Route::post('/replay/update', 'updateReplay')
                ->middleware('auth:sanctum')->name('replay.update');

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
    // 定数マスタ取得
    Route::get('constant', [ConstantController::class, 'show'])->name('constant.index');
});

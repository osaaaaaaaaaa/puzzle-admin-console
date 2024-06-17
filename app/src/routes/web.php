<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ログインページ表示
Route::get('/', [AccountController::class, 'showLoginPage']);

// ログイン処理
Route::post('accounts/doLogin', [AccountController::class, 'doLogin']);

// ログアウト処理
Route::get('accounts/doLogout', [AccountController::class, 'doLogout']);

// ホームページ表示
Route::get('home/index', [AccountController::class, 'showHomePage']);

// アカウント一覧表示
Route::get('accounts/index/{account_id?}', [AccountController::class, 'index']);

// プレイヤー一覧表示
Route::get('players/index/{player_name?}', [PlayerController::class, 'index']);

// アイテム一覧表示
Route::get('items/index', [ItemController::class, 'items_index']);

// インベントリアイテム一覧表示
Route::get('inventoryItems/index/{player_name?}', [ItemController::class, 'inventoryItems_index']);

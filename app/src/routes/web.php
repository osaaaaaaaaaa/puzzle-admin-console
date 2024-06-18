<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ログインページ表示
Route::get('/', [AuthController::class, 'showLoginPage'])->name('login');

// ログイン処理
Route::post('auths/doLogin', [AuthController::class, 'doLogin']);

// ログアウト処理
Route::get('auths/doLogout', [AuthController::class, 'doLogout']);

// ホームページ表示
Route::get('home/index', [AuthController::class, 'showHomePage']);

// アカウント一覧表示
Route::get('accounts/index', [AccountController::class, 'index']);
Route::post('accounts/index/{account_name?}', [AccountController::class, 'index']);

// プレイヤー一覧表示
Route::get('players/index', [PlayerController::class, 'index']);
Route::post('players/index/{player_name?}', [PlayerController::class, 'index']);

// アイテム一覧表示
Route::get('items/index', [ItemController::class, 'items_index']);

// インベントリアイテム一覧表示
Route::get('inventoryItems/index', [ItemController::class, 'inventoryItems_index']);
Route::post('inventoryItems/index/{player_name?}', [ItemController::class, 'inventoryItems_index']);

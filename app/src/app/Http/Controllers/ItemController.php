<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Item;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController
{
    // アイテム一覧表示
    public function items_index(Request $request)
    {
        if ($request->session()->has('login')) {
            // アカウントテーブルから全てのレコードを取得する
            $items = Item::All();
            Debugbar::info($items);
            return view('items/index', ['items' => $items]);
        } else {
            return redirect('/');
        }
    }

    // インベントリのアイテム一覧表示
    public function inventoryItems_index(Request $request)
    {
        // 'login' セッションがあるかどうか
        if ($request->session()->has('login')) {
            // アカウント名の指定があるかどうか
            if (empty($request->player_name)) {// 指定がない場合
                // アカウントテーブルから全てのレコードを取得する
                $inventory_items = Inventory_Item::
                join('players', 'inventory__items.player_id', '=', 'players.id')
                    ->join('items', 'inventory__items.item_id', '=', 'items.id')
                    ->get();
                return view('inventory_items/index', ['inventory_items' => $inventory_items]);
            } else {// 指定がある場合
                // 条件指定してレコードを取得する
                $inventory_items = Inventory_Item::
                join('players', 'inventory__items.player_id', '=', 'players.id')
                    ->join('items', 'inventory__items.item_id', '=', 'items.id')
                    ->where('players.player_name', '=', $request->player_name)
                    ->get();
                return view('inventory_items/index', ['inventory_items' => $inventory_items]);
            }
        } else {
            return redirect('/');
        }
    }
}

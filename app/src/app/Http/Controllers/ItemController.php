<?php

namespace App\Http\Controllers;

use App\Models\Inventory_Item;
use App\Models\Item;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ItemController
{
    // アイテム一覧表示
    public function items_index(Request $request)
    {
        // アカウントテーブルから全てのレコードを取得する
        $items = Item::paginate(20);
        Debugbar::info($items);
        return view('items/index', ['items' => $items]);
    }
}

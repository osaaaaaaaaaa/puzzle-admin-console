<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemController
{
    // アイテム一覧表示
    public function items_index(Request $request)
    {
        if ($request->session()->has('login')) {
            return view('items/index', ['dataList' => $this->getItemData()]);
        } else {
            return redirect('/');
        }
    }

    // インベントリのアイテム一覧表示
    public function inventoryItems_index(Request $request)
    {
        if ($request->session()->has('login')) {
            if (empty($request->player_name)) {
                return view('inventory_items/index', ['dataList' => $this->getInventoryItemData()]);
            } else {
                dd($request->player_name);
            }
        } else {
            return redirect('/');
        }
    }

    // データの取得
    public function getItemData()
    {
        return [
            [
                'id' => 1,
                'item_name' => 'テストアイテム01',
                'type' => '消耗品',
                'effect' => (int)rand(1, 11),
                'description' => 'テストです'
            ],
            [
                'id' => 2,
                'item_name' => 'テストアイテム02',
                'type' => '消耗品',
                'effect' => (int)rand(1, 11),
                'description' => 'テストです'
            ]
        ];
    }

    public function getInventoryItemData()
    {
        return [
            ['id' => 1, 'player_name' => 'test01', 'item_name' => 'テストアイテム01', 'item_cnt' => (int)rand(1, 100)],
            ['id' => 2, 'player_name' => 'test01', 'item_name' => 'テストアイテム02', 'item_cnt' => (int)rand(1, 100)],
            ['id' => 3, 'player_name' => 'test02', 'item_name' => 'テストアイテム01', 'item_cnt' => (int)rand(1, 100)],
            ['id' => 4, 'player_name' => 'test02', 'item_name' => 'テストアイテム02', 'item_cnt' => (int)rand(1, 100)],
            ['id' => 5, 'player_name' => 'test03', 'item_name' => 'テストアイテム01', 'item_cnt' => (int)rand(1, 100)],
            ['id' => 6, 'player_name' => 'test03', 'item_name' => 'テストアイテム02', 'item_cnt' => (int)rand(1, 100)],
        ];
    }
}

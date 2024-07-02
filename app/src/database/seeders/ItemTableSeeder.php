<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
            'name' => '回復ポーション',
            'type' => '消耗品',
            'effect' => 2,
            'description' => 'ライフを回復する'
        ]);
        Item::create([
            'name' => '回復ポーション+',
            'type' => '消耗品',
            'effect' => 4,
            'description' => 'ライフを回復する'
        ]);
        Item::create([
            'name' => '経験値ポーション',
            'type' => '消耗品',
            'effect' => 30,
            'description' => '経験値を獲得する'
        ]);
    }
}

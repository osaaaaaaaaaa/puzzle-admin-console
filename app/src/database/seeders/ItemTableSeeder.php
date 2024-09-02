<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemTableSeeder extends Seeder
{
    const ICON_CNT_MAX = 7;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i <= self::ICON_CNT_MAX; $i++) {
            Item::create([
                'name' => 'ユーザーアイコン' . $i + 1,
                'type' => 1,
                'effect' => $i + 1,
                'description' => 'ユーザーのアイコンデザイン'
            ]);
        }
    }
}

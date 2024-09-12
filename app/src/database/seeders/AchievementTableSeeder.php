<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementTableSeeder extends Seeder
{
    public function run(): void
    {
        Achievement::create([
            'text' => 'レベルを10に上げる',
            'type' => 2,
            'achieved_val' => 10,
            'item_id' => 2,
            'item_amount' => 3
        ]);
        Achievement::create([
            'text' => 'ステージ1をクリアする',
            'type' => 3,
            'achieved_val' => 1,
            'item_id' => 1,
            'item_amount' => 2
        ]);
        Achievement::create([
            'text' => '他のユーザーの救難信号に参加する',
            'type' => 4,
            'achieved_val' => 1,
            'item_id' => 3,
            'item_amount' => 1
        ]);
        Achievement::create([
            'text' => 'アチーブメントを３つ獲得する',
            'type' => 1,
            'achieved_val' => 3,
            'item_id' => 3,
            'item_amount' => 3
        ]);
    }
}

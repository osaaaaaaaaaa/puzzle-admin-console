<?php

namespace Database\Seeders;

use App\Models\UserAchievement;
use Illuminate\Database\Seeder;

class UserAchievementTableSeeder extends Seeder
{
    public function run(): void
    {
        UserAchievement::create([
            'user_id' => 1,
            'achievement_id' => 1,
            'progress_val' => 10,
            'is_receive_item' => 0
        ]);
        UserAchievement::create([
            'user_id' => 1,
            'achievement_id' => 2,
            'progress_val' => 1,
            'is_receive_item' => 1
        ]);
        UserAchievement::create([
            'user_id' => 1,
            'achievement_id' => 3,
            'progress_val' => 1,
            'is_receive_item' => 1
        ]);
        UserAchievement::create([
            'user_id' => 1,
            'achievement_id' => 4,
            'progress_val' => 3,
            'is_receive_item' => 0
        ]);
        UserAchievement::create([
            'user_id' => 2,
            'achievement_id' => 1,
            'progress_val' => 5,
            'is_receive_item' => 0
        ]);
    }
}

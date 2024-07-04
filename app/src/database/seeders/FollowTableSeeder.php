<?php

namespace Database\Seeders;

use App\Models\Follow;
use Illuminate\Database\Seeder;

class FollowTableSeeder extends Seeder
{
    public function run(): void
    {
        Follow::create([
            'user_id' => 1,
            'following_user_id' => 2,
        ]);
        Follow::create([
            'user_id' => 1,
            'following_user_id' => 3,
        ]);
        Follow::create([
            'user_id' => 2,
            'following_user_id' => 1,
        ]);
        Follow::create([
            'user_id' => 3,
            'following_user_id' => 2,
        ]);
    }
}

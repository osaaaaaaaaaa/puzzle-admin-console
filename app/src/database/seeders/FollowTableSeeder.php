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
            'following_id' => 2,
            'is_agreement' => 1,
        ]);
        Follow::create([
            'user_id' => 1,
            'following_id' => 3,
            'is_agreement' => 0,
        ]);
        Follow::create([
            'user_id' => 2,
            'following_id' => 1,
            'is_agreement' => 1,
        ]);
        Follow::create([
            'user_id' => 3,
            'following_id' => 2,
            'is_agreement' => 0,
        ]);
    }
}

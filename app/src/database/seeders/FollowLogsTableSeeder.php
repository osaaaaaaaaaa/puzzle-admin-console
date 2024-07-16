<?php

namespace Database\Seeders;

use App\Models\FollowLogs;
use Illuminate\Database\Seeder;

class FollowLogsTableSeeder extends Seeder
{
    public function run(): void
    {
        FollowLogs::create([
            'user_id' => 1,
            'target_user_id' => 2,
            'action' => 1,
        ]);
        FollowLogs::create([
            'user_id' => 1,
            'target_user_id' => 2,
            'action' => 0,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Replay;
use Illuminate\Database\Seeder;

class ReplayTableSeeder extends Seeder
{
    public function run(): void
    {
        Replay::create([
            'distress_signal_id' => 1,
            'replay_data' => '',
            'guest_data' => '',
        ]);
    }
}

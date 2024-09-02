<?php

namespace Database\Seeders;

use App\Models\StageResult;
use Illuminate\Database\Seeder;

class StageResultTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 10; $j++) {
                StageResult::create([
                    'user_id' => $i,
                    'is_medal1' => mt_rand(0, 1),
                    'is_medal2' => mt_rand(0, 1),
                    'stage_id' => $j,
                    'score' => mt_rand(1, 1000)
                ]);
            }
        }
    }
}

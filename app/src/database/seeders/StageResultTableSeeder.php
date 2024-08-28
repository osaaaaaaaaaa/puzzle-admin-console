<?php

namespace Database\Seeders;

use App\Models\StageResult;
use Illuminate\Database\Seeder;

class StageResultTableSeeder extends Seeder
{
    public function run(): void
    {
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
        StageResult::create([
            'user_id' => mt_rand(1, 10),
            'stage_id' => mt_rand(1, 40),
            'score' => mt_rand(1, 1000)
        ]);
    }
}

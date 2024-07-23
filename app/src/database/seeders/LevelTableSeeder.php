<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelTableSeeder extends Seeder
{
    public function run(): void
    {
        $levelMax = 50;

        for ($i = 0; $i < $levelMax; $i++) {
            Level::create([
                'level' => $i + 1,
                'exp' => 100 * $i
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Constant;
use Illuminate\Database\Seeder;

class ConstantTableSeeder extends Seeder
{
    public function run(): void
    {
        Constant::create([
            'constant' => 22,
            'type' => 1,
        ]);
        Constant::create([
            'constant' => 30,
            'type' => 2,
        ]);
    }
}

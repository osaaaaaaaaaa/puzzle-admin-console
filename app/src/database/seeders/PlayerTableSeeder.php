<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlayerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // データを挿入
        Player::create([
            'player_name' => 'test001',
            'level' => 10,
            'exp' => rand(0, 1000),
            'life' => 1
        ]);
        Player::create([
            'player_name' => 'test011',
            'level' => 20,
            'exp' => rand(0, 1000),
            'life' => 5
        ]);
        Player::create([
            'player_name' => 'test002',
            'level' => 50,
            'exp' => rand(0, 1000),
            'life' => 10
        ]);
        Player::create([
            'player_name' => 'test022',
            'level' => 100,
            'exp' => rand(0, 1000),
            'life' => 20
        ]);
    }
}

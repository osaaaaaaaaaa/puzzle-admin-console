<?php

namespace Database\Seeders;

use App\Models\Guest;
use Illuminate\Database\Seeder;

class GuestTableSeeder extends Seeder
{
    public function run(): void
    {
        Guest::create([
            'distress_signal_id' => 1,
            'user_id' => 1,
            'position' => '',
            'vector' => '',
        ]);
    }
}

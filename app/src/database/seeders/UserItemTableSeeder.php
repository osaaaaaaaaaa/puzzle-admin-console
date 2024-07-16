<?php

namespace Database\Seeders;

use App\Models\UserItem;
use Illuminate\Database\Seeder;

class UserItemTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i < 5; $i++) {
            UserItem::create([
                'user_id' => $i,
                'item_id' => 1,
                'amount' => rand(0, 100)
            ]);
            UserItem::create([
                'user_id' => $i,
                'item_id' => 2,
                'amount' => rand(0, 100)
            ]);
            UserItem::create([
                'user_id' => $i,
                'item_id' => 3,
                'amount' => rand(0, 100)
            ]);
        }
    }
}

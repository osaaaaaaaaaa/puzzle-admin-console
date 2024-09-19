<?php

namespace Database\Seeders;

use App\Models\UserItem;
use Illuminate\Database\Seeder;

class UserItemTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i < 10; $i++) {
            UserItem::create([
                'user_id' => $i,
                'item_id' => 10,
                'amount' => 1
            ]);
            UserItem::create([
                'user_id' => $i,
                'item_id' => 11,
                'amount' => 1
            ]);
        }
    }
}

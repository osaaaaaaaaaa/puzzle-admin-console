<?php

namespace Database\Seeders;

use App\Models\Inventory_Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 5; $i++) {
            Inventory_Item::create([
                'user_id' => $i,
                'item_id' => 1,
                'amount' => rand(0, 100)
            ]);
            Inventory_Item::create([
                'user_id' => $i,
                'item_id' => 2,
                'amount' => rand(0, 100)
            ]);
            Inventory_Item::create([
                'user_id' => $i,
                'item_id' => 3,
                'amount' => rand(0, 100)
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Attached_Item;
use Illuminate\Database\Seeder;

class AttachedItemTableSeeder extends Seeder
{
    public function run(): void
    {
        Attached_Item::create([
                'item_id' => 37,
                'amount' => 5,
                'mail_id' => 1
            ]
        );
    }
}

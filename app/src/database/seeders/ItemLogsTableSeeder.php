<?php

namespace Database\Seeders;

use App\Models\ItemLogs;
use Illuminate\Database\Seeder;

class ItemLogsTableSeeder extends Seeder
{
    public function run(): void
    {
        ItemLogs::create([
            'user_id' => 1,
            'item_id' => 1,
            'allie_count' => 1,
            'option_id' => 1
        ]);
    }
}

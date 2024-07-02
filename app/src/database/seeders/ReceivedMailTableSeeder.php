<?php

namespace Database\Seeders;

use App\Models\Received_Mail;
use Illuminate\Database\Seeder;

class ReceivedMailTableSeeder extends Seeder
{
    public function run(): void
    {
        Received_Mail::create([
            'user_id' => 1,
            'mail_id' => 1,
            'is_received' => 0
        ]);
    }
}

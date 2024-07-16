<?php

namespace Database\Seeders;

use App\Models\UserMail;
use Illuminate\Database\Seeder;

class UserMailTableSeeder extends Seeder
{
    public function run(): void
    {
        UserMail::create([
            'user_id' => 1,
            'mail_id' => 1,
            'is_received' => 1
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Mail;
use Illuminate\Database\Seeder;

class MailTableSeeder extends Seeder
{
    public function run(): void
    {
        Mail::create([
            'text' => 'テストメール',
        ]);
    }
}

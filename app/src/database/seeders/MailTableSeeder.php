<?php

namespace Database\Seeders;

use App\Models\Mail;
use Illuminate\Database\Seeder;

class MailTableSeeder extends Seeder
{
    public function run(): void
    {
        Mail::create([
            'title' => '募集を削除された救難信号の報酬について',
            'text' => 'いつもプレイしていただき、ありがとうございます。' . "\n" . 'プレイヤー様が参加していた救難信号の募集はホストによって削除されました。' . "\n" . '削除された募集はクリア済のため報酬をお届けします。ぜひお受け取り下さい。'
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AchievementTableSeeder extends Seeder
{
    const STAGE_MAX_CNT = 22;
    const POINT_AMOUNT = 10;

    // [タイプ] 1:ステージクリア, 2:スコア, 3:ポイント報酬
    public function run(): void
    {
        for ($i = 0; $i < self::STAGE_MAX_CNT; $i++) {
            Achievement::create([
                'text' => 'ステージ' . ($i + 1) . 'を初回クリアしよう',
                'type' => 1,
                'achieved_val' => $i + 1,
                'item_id' => 37,
                'item_amount' => self::POINT_AMOUNT
            ]);
            Achievement::create([
                'text' => 'トータルスコア' . (1200 * ($i + 1)) . 'を達成しよう',
                'type' => 2,
                'achieved_val' => 1200 * ($i + 1),
                'item_id' => 37,
                'item_amount' => self::POINT_AMOUNT
            ]);
        }

        // Storageファザードを使って読み込む
        $content = Storage::get('txt/Achievement.txt');

        // 区切り文字で分割する(改行指定)
        $lines = explode(PHP_EOL, $content);
        array_splice($lines, 0, 1); // 1行目は削除

        $val = 30;
        foreach ($lines as $line) {
            // 改行を削除
            str_replace([PHP_EOL, " "], "", $line);
            Achievement::create([
                'text' => 'ポイント報酬',
                'type' => 3,
                'achieved_val' => $val,
                'item_id' => (int)$line,
                'item_amount' => 1
            ]);
            $val += 30;
        }

    }
}

<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ItemTableSeeder extends Seeder
{
    const ICON_CNT_MAX = 9;

    /*[アイテムタイプ]
    1:アイコン,
    2:称号,
    3:お助けアイテム,
    4:救難信号解放,
    5:救難信号の上限値UP,
    6:ポイント*/

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // テキストファイルのパス
        $filePath = 'txt/Item.txt';

        // Storageファザードを使って読み込む
        $content = Storage::get($filePath);

        // 区切り文字で分割する(改行指定)
        $lines = explode(PHP_EOL, $content);
        array_splice($lines, 0, 1); // 1行目は削除

        foreach ($lines as $line) {
            // 改行と空白を削除
            str_replace([PHP_EOL, " "], "", $line);
            $values = explode(",", $line);

            Item::create([
                'name' => $values[0],
                'type' => (int)$values[1],
                'effect' => (int)$values[2],
                'description' => $values[3]
            ]);
        }
    }
}

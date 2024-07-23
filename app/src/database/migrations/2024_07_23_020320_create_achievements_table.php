<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string("title", 20);    // アチーブメントのタイトル
            $table->string("text", 40);     // テキスト
            $table->integer("type_id");           // 種類ID [1:レベル,2:ステージ,3:救難信号]
            $table->integer("achieved_val");      // 条件達成値
            $table->integer("item_id");           // 報酬アイテムID
            $table->integer("item_amount");       // 報酬アイテム個数
            $table->timestamps();

            // インデックス設定
            $table->index('type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string("text", 40);     // 達成条件テキスト
            $table->integer("type");              // 種類No. [1:ステージ,2:トータルスコア]
            $table->integer("achieved_val");      // 条件達成値
            $table->integer("item_id");           // 報酬アイテムID
            $table->integer("item_amount");       // 報酬アイテム個数
            $table->timestamps();

            // インデックス設定
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};

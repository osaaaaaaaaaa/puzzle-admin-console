<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');         // ユーザーID
            $table->integer('item_id');         // アイテムID
            $table->integer('option_id');       // 入手した方法
            $table->integer('allie_count');     // 増減数
            $table->timestamps();

            // インデックス追加
            $table->index(['user_id', 'item_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_logs');
    }
};

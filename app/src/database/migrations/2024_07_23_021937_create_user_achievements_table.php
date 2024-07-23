<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');         // ユーザーID
            $table->integer('achievement_id');  // アチーブメントID
            $table->integer('progress_val');    // 進捗の値
            $table->boolean('is_achieved');     // 達成したかどうか
            $table->timestamps();

            // インデックス設定
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};

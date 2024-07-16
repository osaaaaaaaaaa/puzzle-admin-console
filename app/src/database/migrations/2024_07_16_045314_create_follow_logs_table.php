<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('follow_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');         // 自身のユーザーID
            $table->integer('target_user_id');  // フォロー対象者のID
            $table->boolean('action');          // 1:登録,0:解除
            $table->timestamps();

            // インデックス追加
            $table->index(['user_id', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_logs');
    }
};

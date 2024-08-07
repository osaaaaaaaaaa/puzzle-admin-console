<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('following_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');              // 自身のユーザーID
            $table->integer('following_user_id');    // 相手のユーザーID
            $table->timestamps();

            // 複合ユニーク制約
            $table->unique(['user_id', 'following_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('following_users');
    }
};

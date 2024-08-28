<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);  // 名前
            $table->integer('achievement_id'); // 設定しているアチーブメントID
            $table->integer('stage_id');       // 最新のステージID
            $table->integer('icon_id');        // アイコンID
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

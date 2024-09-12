<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);              // 名前
            $table->integer('title_id');                    // 設定している称号ID
            $table->integer('icon_id');                     // アイコンID
            $table->integer('stage_id');                    // ステージID
            $table->boolean('is_distress_signal_enabled');  // 救難信号システムを解放したかどうか
            $table->integer('add_distress_signals');        // 救難信号の募集や参加できる上限数に加算する値
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

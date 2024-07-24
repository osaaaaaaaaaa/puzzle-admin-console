<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->integer("distress_signal_id");  // 救難信号のID
            $table->integer("user_id");             // ゲストユーザーのID
            $table->string("position");             // 配置したXY座標(json文字列)
            $table->string("vector");               // ベクトルXY座標(json文字列)
            $table->timestamps();

            // インデックス設定
            $table->index('distress_signal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};

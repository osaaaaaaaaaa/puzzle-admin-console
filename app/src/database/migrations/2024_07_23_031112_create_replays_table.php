<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('replays', function (Blueprint $table) {
            $table->id();
            $table->integer('distress_signal_id');  // 救難信号のID
            $table->string('replay_data');          // リプレイの情報（json文字列）：ER図参照
            $table->string('guest_data');           // ホストから見た参加ゲストの情報（json文字列）：ER図参照
            $table->timestamps();

            // インデックス設定
            $table->index('distress_signal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replays');
    }
};

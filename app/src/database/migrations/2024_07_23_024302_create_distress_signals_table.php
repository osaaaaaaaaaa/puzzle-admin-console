<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('distress_signals', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");         // ホストユーザーのID
            $table->integer("stage_id");        // ステージID
            $table->integer("action");          // 進捗状況 [0:挑戦中,1:ゲームクリア]
            $table->timestamps();

            // インデックス設定
            $table->index('user_id');
            $table->index('stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distress_signals');
    }
};

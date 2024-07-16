<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');     // ユーザーID
            $table->integer('mail_id');     // メールID
            $table->boolean('action');      // 1:開封済み,0:未開封
            $table->timestamps();

            // インデックス追加
            $table->index(['user_id', 'mail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};

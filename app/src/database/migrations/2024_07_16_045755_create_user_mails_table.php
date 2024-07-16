<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_mails', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('mail_id');
            $table->boolean('is_received',);
            $table->timestamps();

            // インデックス追加
            $table->index(['user_id', 'mail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mails');
    }
};

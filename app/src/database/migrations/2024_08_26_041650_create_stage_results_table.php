<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stage_results', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('stage_id');
            $table->boolean('is_medal1');
            $table->boolean('is_medal2');
            $table->float('time');
            $table->integer('score');
            $table->timestamps();

            // ユニーク制約
            $table->unique(['user_id', 'stage_id']);
            $table->index('user_id');
            $table->index('stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->integer('level');
            $table->integer('exp');
            $table->integer('life');
            $table->timestamps();

            // ユニーク制約設定
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

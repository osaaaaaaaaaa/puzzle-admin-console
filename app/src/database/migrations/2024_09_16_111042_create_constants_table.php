<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('constants', function (Blueprint $table) {
            $table->id();
            $table->integer('constant');
            $table->integer('type');        // [1:ステージ数,2:フォロー最大人数]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constants');
    }
};

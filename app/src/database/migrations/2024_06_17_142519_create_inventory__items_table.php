<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory__items', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('item_id');
            $table->integer('item_cnt');
            $table->timestamps();

            // ユニーク制約設定
            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory__items');
    }
};

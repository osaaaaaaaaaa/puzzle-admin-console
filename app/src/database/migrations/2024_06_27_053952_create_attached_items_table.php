<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attached__items', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->integer('amount');
            $table->integer('mail_id');
            $table->timestamps();

            $table->index(['item_id', 'mail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached__items');
    }
};

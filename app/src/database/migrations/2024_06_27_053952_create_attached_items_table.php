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
            $table->integer('cnt');
            $table->integer('mail_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached__items');
    }
};

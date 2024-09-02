<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ng_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ng_words');
    }
};

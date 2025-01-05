<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bingo_words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bingo_id')->index('bingo_words_bingo_id_foreign');
            $table->string('word', 30)->nullable();
            $table->string('definition', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bingo_words');
    }
};

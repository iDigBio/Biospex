<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBingoWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bingo_words', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id')->index('bingo_words_bingo_id_foreign');
            $table->string('word', 30)->nullable();
            $table->string('definition', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bingo_words');
    }
}

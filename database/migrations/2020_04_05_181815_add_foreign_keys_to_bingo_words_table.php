<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBingoWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bingo_words', function (Blueprint $table) {
            $table->foreign('bingo_id')->references('id')->on('bingos')->onUpdate('CASCADE')->onDelete('CASCADE');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bingo_words', function (Blueprint $table) {
            $table->dropForeign('bingo_words_bingo_id_foreign');
        });
    }
}

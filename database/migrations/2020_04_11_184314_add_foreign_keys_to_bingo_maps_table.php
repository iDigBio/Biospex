<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBingoMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bingo_maps', function (Blueprint $table) {
            $table->foreign('bingo_id')->references('id')->on('bingos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bingo_maps', function (Blueprint $table) {
            $table->dropForeign('bingo_maps_bingo_id_foreign');
        });
    }
}

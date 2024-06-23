<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBingoMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bingo_maps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bingo_id')->index('bingo_maps_bingo_id_foreign');
            $table->binary('uuid');
            $table->binary('ip');
            $table->double('latitude', 8, 6);
            $table->double('longitude', 8, 6);
            $table->string('city', 100);
            $table->boolean('winner')->default(0);
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
        Schema::dropIfExists('bingo_maps');
    }
}

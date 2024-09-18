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
        Schema::create('bingo_maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bingo_id')->index('bingo_maps_bingo_id_foreign');
            $table->uuid('uuid')->index();
            $table->string('ip', 30);
            $table->double('latitude');
            $table->double('longitude');
            $table->string('city', 100);
            $table->boolean('winner')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bingo_maps');
    }
};

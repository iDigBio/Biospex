<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geolocate_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expedition_id')->unique();
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->string('file_path');
            $table->json('properties');
            $table->boolean('exported')->default(0);
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
        Schema::dropIfExists('geolocate_forms');
    }
};

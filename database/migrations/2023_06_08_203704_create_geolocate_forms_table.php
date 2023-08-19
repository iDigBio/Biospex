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
            $table->unsignedInteger('group_id')->unique();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->string('name');
            $table->json('properties');
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

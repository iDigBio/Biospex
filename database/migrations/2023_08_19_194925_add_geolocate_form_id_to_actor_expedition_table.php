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
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->unsignedInteger('geolocate_form_id')->index('expedition_actor_geolocate_form_id_foreign')->after('actor_id')->nullable();
            $table->foreign('geolocate_form_id', 'expedition_actor_geolocate_form_id_foreign')->references('id')->on('geolocate_forms')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            //
        });
    }
};

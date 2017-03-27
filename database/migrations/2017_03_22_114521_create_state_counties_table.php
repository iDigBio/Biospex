<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateCountiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_counties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('county_name');
            $table->string('state_county');
            $table->string('state_abbr');
            $table->string('state_abbr_cap');
            $table->text('geometry');
            $table->string('value');
            $table->string('geo_id');
            $table->string('geo_id_2');
            $table->string('geographic_name');
            $table->string('state_num');
            $table->string('county_num');
            $table->string('fips_forumla');
            $table->string('has_error');
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('state_county');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('state_counties');
    }
}

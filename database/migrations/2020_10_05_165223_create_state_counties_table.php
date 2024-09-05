<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('county_name', 255)->nullable();
            $table->string('state_county', 255)->nullable()->index();
            $table->string('state_abbr', 255)->nullable();
            $table->string('state_abbr_cap', 255)->nullable();
            $table->text('geometry')->nullable();
            $table->string('value', 255)->nullable();
            $table->string('geo_id', 255)->nullable();
            $table->string('geo_id_2', 255)->nullable();
            $table->string('geographic_name', 255)->nullable();
            $table->string('state_num', 255)->nullable()->index('state_num');
            $table->string('county_num', 255)->nullable();
            $table->string('fips_forumla', 255)->nullable();
            $table->string('has_error', 255)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
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

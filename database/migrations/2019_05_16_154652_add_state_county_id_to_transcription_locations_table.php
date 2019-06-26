<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateCountyIdToTranscriptionLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('transcription_locations')->truncate();
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->integer('state_county_id')->unsigned()->after('expedition_id');
            $table->foreign('state_county_id')->references('id')->on('state_counties')->onUpdate('cascade')->onDelete('cascade');
            $table->dropColumn('state_province');
            $table->dropColumn('county');
            $table->dropColumn('state_county');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->dropForeign(['state_county_id']);
            $table->dropColumn('state_county_id');
            $table->string('state_province')->nullable();
            $table->string('county')->nullable();
            $table->string('state_county')->nullable();
        });
        Schema::enableForeignKeyConstraints();
    }
}

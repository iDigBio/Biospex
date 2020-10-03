<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTranscriptionLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('project_id')->references('id')->on('projects')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('state_county_id')->references('id')->on('state_counties')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->dropForeign('transcription_locations_expedition_id_foreign');
            $table->dropForeign('transcription_locations_project_id_foreign');
            $table->dropForeign('transcription_locations_state_county_id_foreign');
        });
    }
}

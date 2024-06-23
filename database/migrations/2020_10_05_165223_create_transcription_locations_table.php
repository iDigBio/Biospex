<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscriptionLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcription_locations', function (Blueprint $table) {
            $table->id();
            $table->integer('classification_id')->unique();
            $table->unsignedBigInteger('project_id')->index('transcription_locations_project_id_foreign');
            $table->unsignedBigInteger('expedition_id')->index('transcription_locations_expedition_id_foreign');
            $table->unsignedBigInteger('state_county_id')->index('transcription_locations_state_county_id_foreign');
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
        Schema::dropIfExists('transcription_locations');
    }
}

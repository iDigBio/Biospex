<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->integer('classification_id');
            $table->integer('project_id')->unsigned();
            $table->integer('expedition_id')->unsigned();
            $table->string('state_province')->nullable();
            $table->string('county')->nullable();
            $table->string('state_county')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
            $table->index('state_county');
            $table->unique('classification_id');
            $table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
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

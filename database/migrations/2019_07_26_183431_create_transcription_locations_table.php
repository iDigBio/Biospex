<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTranscriptionLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transcription_locations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('classification_id')->unique();
			$table->integer('project_id')->unsigned()->index('transcription_locations_project_id_foreign');
			$table->integer('expedition_id')->unsigned()->index('transcription_locations_expedition_id_foreign');
			$table->integer('state_county_id')->unsigned()->index('transcription_locations_state_county_id_foreign');
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
		Schema::drop('transcription_locations');
	}

}

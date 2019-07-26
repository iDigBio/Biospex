<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTranscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_transcriptions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('classification_id');
			$table->integer('event_id')->unsigned()->index('event_transcriptions_event_id_foreign');
			$table->integer('team_id')->unsigned()->index('event_transcriptions_team_id_foreign');
			$table->integer('user_id')->unsigned()->index('event_transcriptions_user_id_foreign');
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
		Schema::drop('event_transcriptions');
	}

}

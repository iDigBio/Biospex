<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTeamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_teams', function(Blueprint $table)
		{
			$table->increments('id');
			$table->binary('uuid', 16)->nullable();
			$table->integer('event_id')->unsigned();
			$table->string('title', 191)->nullable();
			$table->timestamps();
			$table->unique(['event_id','title'], 'event_team_title');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event_teams');
	}

}

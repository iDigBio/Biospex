<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventTeamUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_team_user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('team_id')->unsigned()->index('event_team_user_team_id_foreign');
			$table->integer('user_id')->unsigned()->index('event_team_user_user_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event_team_user');
	}

}

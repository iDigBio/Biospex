<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEventTeamUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event_team_user', function(Blueprint $table)
		{
			$table->foreign('team_id')->references('id')->on('event_teams')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id')->references('id')->on('event_users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event_team_user', function(Blueprint $table)
		{
			$table->dropForeign('event_team_user_team_id_foreign');
			$table->dropForeign('event_team_user_user_id_foreign');
		});
	}

}

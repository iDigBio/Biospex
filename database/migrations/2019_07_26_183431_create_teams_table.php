<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTeamsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('teams', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('team_category_id')->unsigned()->index('teams_team_category_id_foreign');
			$table->string('first_name', 50)->nullable();
			$table->string('last_name', 50)->nullable();
			$table->string('email')->nullable();
			$table->string('title', 191)->nullable();
			$table->string('department', 191)->nullable();
			$table->string('institution')->nullable();
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
		Schema::drop('teams');
	}

}

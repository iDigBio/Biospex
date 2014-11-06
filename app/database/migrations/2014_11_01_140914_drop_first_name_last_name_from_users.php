<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropFirstNameLastNameFromUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$users = DB::select('select * from users');
			if ($users)
			{
				foreach ($users as $user)
				{
					DB::update('update profiles set user_id = ?, first_name = ?, last_name = ?', [$user->id, $user->first_name, $user->last_name]);
				}
			}

			$table->dropColumn(['first_name', 'last_name']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
		});
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profiles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('profiles_user_id_foreign');
			$table->string('first_name', 50)->nullable();
			$table->string('last_name', 50)->nullable();
			$table->string('timezone')->nullable();
			$table->timestamps();
			$table->string('avatar_file_name')->nullable();
			$table->integer('avatar_file_size')->nullable();
			$table->string('avatar_content_type')->nullable();
			$table->dateTime('avatar_updated_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('profiles');
	}

}

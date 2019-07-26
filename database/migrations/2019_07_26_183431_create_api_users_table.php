<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->nullable();
			$table->string('email')->nullable()->unique('api_subscribers_email_unique');
			$table->string('password', 60)->nullable();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('reset_password_code')->nullable()->index('api_subscribers_reset_password_code_index');
			$table->string('remember_token', 100)->nullable();
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
		Schema::drop('api_users');
	}

}

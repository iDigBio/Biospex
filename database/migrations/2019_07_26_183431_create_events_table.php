<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('events', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned()->index('events_project_id_foreign');
			$table->integer('owner_id')->unsigned()->index('events_owner_id_foreign');
			$table->string('title', 191)->nullable();
			$table->string('description', 104)->nullable();
			$table->string('hashtag')->nullable();
			$table->string('contact', 191)->nullable();
			$table->string('contact_email', 191)->nullable();
			$table->dateTime('start_date')->nullable();
			$table->dateTime('end_date')->nullable();
			$table->string('timezone', 191)->nullable();
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
		Schema::drop('events');
	}

}

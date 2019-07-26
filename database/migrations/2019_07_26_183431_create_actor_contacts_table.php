<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActorContactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actor_contacts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('actor_id')->unsigned()->index('actor_contacts_actor_id_foreign');
			$table->string('email')->nullable();
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
		Schema::drop('actor_contacts');
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToActorContactsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('actor_contacts', function(Blueprint $table)
		{
			$table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('actor_contacts', function(Blueprint $table)
		{
			$table->dropForeign('actor_contacts_actor_id_foreign');
		});
	}

}

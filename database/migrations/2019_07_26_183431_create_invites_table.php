<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invites', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('group_id')->unsigned();
			$table->string('email')->nullable();
			$table->string('code')->nullable()->index();
			$table->timestamps();
			$table->index(['group_id','email']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invites');
	}

}

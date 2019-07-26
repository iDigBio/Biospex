<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActorExpeditionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actor_expedition', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('expedition_id')->unsigned()->index('expedition_actor_expedition_id_foreign');
			$table->integer('actor_id')->unsigned()->index('expedition_actor_actor_id_foreign');
			$table->boolean('state')->default(0);
			$table->integer('total')->default(0);
			$table->integer('processed')->default(0)->index();
			$table->integer('error')->default(0);
			$table->integer('queued')->default(0);
			$table->integer('completed')->default(0);
			$table->integer('order')->default(0);
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
		Schema::drop('actor_expedition');
	}

}

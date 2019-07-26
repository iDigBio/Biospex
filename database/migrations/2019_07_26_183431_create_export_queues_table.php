<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExportQueuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('export_queues', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('expedition_id')->unsigned();
			$table->integer('actor_id')->unsigned()->index('export_queues_actor_id_foreign');
			$table->integer('stage')->default(0)->index();
			$table->boolean('queued')->default(0)->index();
			$table->boolean('error')->default(0)->index();
			$table->text('missing')->nullable();
			$table->timestamps();
			$table->unique(['expedition_id','actor_id','stage']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('export_queues');
	}

}

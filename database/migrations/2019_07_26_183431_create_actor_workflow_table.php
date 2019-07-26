<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActorWorkflowTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actor_workflow', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('workflow_id')->unsigned()->index('actor_workflow_workflow_id_foreign');
			$table->integer('actor_id')->unsigned()->index('actor_workflow_actor_id_foreign');
			$table->integer('order')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('actor_workflow');
	}

}

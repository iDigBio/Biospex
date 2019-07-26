<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWorkflowManagersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('workflow_managers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('expedition_id')->unsigned()->index('workflow_manager_expedition_id_foreign');
			$table->boolean('stopped')->default(0)->index('workflow_manager_stopped_index');
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
		Schema::drop('workflow_managers');
	}

}

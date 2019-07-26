<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToWorkflowManagersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('workflow_managers', function(Blueprint $table)
		{
			$table->foreign('expedition_id', 'workflow_manager_expedition_id_foreign')->references('id')->on('expeditions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('workflow_managers', function(Blueprint $table)
		{
			$table->dropForeign('workflow_manager_expedition_id_foreign');
		});
	}

}

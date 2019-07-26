<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNfnWorkflowsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nfn_workflows', function(Blueprint $table)
		{
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nfn_workflows', function(Blueprint $table)
		{
			$table->dropForeign('nfn_workflows_expedition_id_foreign');
			$table->dropForeign('nfn_workflows_project_id_foreign');
		});
	}

}

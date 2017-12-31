<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddWorkflowsToProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::disableForeignKeys();
        if (Schema::hasColumn('projects', 'workflow_id'))
        {
            return;
        }
		Schema::table('projects', function (Blueprint $table) {
			$table->unsignedInteger('workflow_id')->after('language_skills');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});
        Schema::enableForeignKeys();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function (Blueprint $table) {
			$table->dropIndex('projects_workflow_id_index');
			$table->dropForeign('projects_workflow_id_foreign');
			$table->dropColumn('workflow_id');
		});
	}

}

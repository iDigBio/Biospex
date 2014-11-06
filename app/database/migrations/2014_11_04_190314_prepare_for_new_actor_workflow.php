<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareForNewActorWorkflow extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		// Rename tables and create expedition_actor
		Schema::rename('workflows', 'actors');
		Schema::rename('project_workflow', 'project_actor');
		Schema::create('expedition_actor', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('expedition_id');
			$table->unsignedInteger('actor_id');
			$table->tinyInteger('state')->default(0);
			$table->integer('completed')->default(0);
			$table->timestamps();

			$table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
		});

		// Create proper indexes and keys on project_workflow
		Schema::table('project_actor', function (Blueprint $table)
		{
			$table->dropIndex('project_workflow_workflow_id_foreign');
			$table->dropIndex('project_workflow_project_id_foreign');
			$table->dropForeign('project_workflow_workflow_id_foreign');
			$table->dropForeign('project_workflow_project_id_foreign');
			$table->renameColumn('workflow_id', 'actor_id');
			$table->tinyInteger('order_by')->index()->default(0);
			$table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		// Create proper indexes on workflow_manager and add columns
		Schema::table('workflow_manager', function(Blueprint $table)
		{
			$table->dropIndex('workflow_manager_workflow_id_foreign');
			$table->dropForeign('workflow_manager_workflow_id_foreign');
			$table->dropTimestamps();
			$table->dropSoftDeletes();
			$table->dropColumn('workflow_id');
			$table->tinyInteger('stopped')->index()->default(0);
			$table->tinyInteger('error')->index()->default(0);
		});

		// Rename workflow_id and create foreign key for actors
		Schema::table('downloads', function(Blueprint $table)
		{
			$table->dropIndex('downloads_workflow_id_foreign');
			$table->dropForeign('downloads_workflow_id_foreign');
			$table->renameColumn('workflow_id', 'actor_id');
			$table->dropColumn('count');
			$table->unsignedInteger('count')->default(0)->after('file');
			$table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
		});

		// Select any existing records from workflow_manager and insert into expedition_actor
		$results = DB::select('SELECT * FROM workflow_manager');
		if ($results)
		{
			foreach ($results as $result)
			{
				$expedition = DB::select('SELECT * FROM expeditions WHERE id = ?', [$result->expedition_id]);
				$fields = "expedition_id, actor_id, state, completed";
				$values = [$result->expedition_id, $result->actor_id, $expedition->state, $expedition->completed];
				DB::insert('INSERT INTO expedition_actor (' . $fields . ') VALUES (?,?,?,?)', [$values]);
			}
		}

		// Drop state, completed columns
		Schema::table('expeditions', function(Blueprint $table)
		{
			$table->dropColumn(['state', 'completed']);
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		// Rename tables and drop expedition_actor
		Schema::rename('actors', 'workflows');
		Schema::rename('project_actor', 'project_workflow');
		Schema::drop('expedition_actor');

		// Reconfigure
		Schema::table('project_workflow', function (Blueprint $table)
		{
			$table->dropIndex('project_actor_order_by_index');
			$table->dropIndex('project_actor_actor_id_foreign');
			$table->dropIndex('project_actor_project_id_foreign');
			$table->dropForeign('project_actor_actor_id_foreign');
			$table->dropForeign('project_actor_project_id_foreign');
			$table->dropColumn('order_by');
			$table->renameColumn('actor_id', 'workflow_id');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		Schema::table('workflow_manager', function(Blueprint $table)
		{
			$table->dropIndex('workflow_manager_stopped_index');
			$table->dropIndex('workflow_manager_error_index');
			$table->dropColumn('stopped');
			$table->dropColumn('error');
			$table->unsignedInteger('workflow_id');
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});

		Schema::table('downloads', function(Blueprint $table)
		{
			$table->dropIndex('downloads_actor_id_foreign');
			$table->dropForeign('downloads_actor_id_foreign');
			$table->renameColumn('actor_id', 'workflow_id');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});

		Schema::table('expeditions', function(Blueprint $table)
		{
			$table->tinyInteger('state')->default(0);
			$table->integer('completed')->default(0);
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}

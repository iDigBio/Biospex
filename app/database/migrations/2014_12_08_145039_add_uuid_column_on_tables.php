<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUuidColumnOnTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function(Blueprint $table)
		{
			$table->char('uuid', 36)->after('id');
		});

		Schema::table('expeditions', function(Blueprint $table)
		{
			$table->char('uuid', 36)->after('id');
			$table->char('project_uuid', 36)->after('uuid');
		});

		Schema::table('headers', function(Blueprint $table)
		{
			$table->char('project_uuid', 36)->after('id');
		});

		Schema::table('imports', function(Blueprint $table)
		{
			$table->char('project_uuid', 36)->after('id');
		});

		Schema::table('metas', function(Blueprint $table)
		{
			$table->char('project_uuid', 36)->after('id');
		});

		Schema::table('project_actor', function(Blueprint $table)
		{
			$table->char('project_uuid', 36)->after('id');
		});

		Schema::table('downloads', function(Blueprint $table)
		{
			$table->char('expedition_uuid', 36)->after('id');
		});

		Schema::table('expedition_actor', function(Blueprint $table)
		{
			$table->char('expedition_uuid', 36)->after('id');
		});

		Schema::table('workflow_manager', function(Blueprint $table)
		{
			$table->char('expedition_uuid', 36)->after('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function(Blueprint $table)
		{
			$table->dropColumn('uuid');
		});

		Schema::table('expeditions', function(Blueprint $table)
		{
			$table->dropColumn('uuid');
			$table->dropColumn('project_uuid');
		});

		Schema::table('headers', function(Blueprint $table)
		{
			$table->dropColumn('project_uuid');
		});

		Schema::table('imports', function(Blueprint $table)
		{
			$table->dropColumn('project_uuid');
		});

		Schema::table('metas', function(Blueprint $table)
		{
			$table->dropColumn('project_uuid');
		});

		Schema::table('project_actor', function(Blueprint $table)
		{
			$table->dropColumn('project_uuid');
		});

		Schema::table('downloads', function(Blueprint $table)
		{
			$table->dropColumn('expedition_uuid');
		});

		Schema::table('expedition_actor', function(Blueprint $table)
		{
			$table->dropColumn('expedition_uuid');
		});

		Schema::table('workflow_manager', function(Blueprint $table)
		{
			$table->dropColumn('expedition_uuid');
		});
	}

}

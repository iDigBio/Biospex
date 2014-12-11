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
		DB::statement("ALTER TABLE projects ADD uuid BINARY(16) NULL AFTER id");
		DB::statement("ALTER TABLE expeditions ADD uuid BINARY(16) NULL AFTER id");
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
		});
	}

}

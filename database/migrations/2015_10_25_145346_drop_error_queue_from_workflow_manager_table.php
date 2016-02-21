<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropErrorQueueFromWorkflowManagerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('workflow_manager', function (Blueprint $table) {
			$table->dropColumn('error');
			$table->dropColumn('queue');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('workflow_manager', function (Blueprint $table) {
			$table->integer('error')->default(0)->after('stopped');
			$table->integer('queue')->default(0)->after('error');
		});
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBatchColumnToOcrQueue extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ocr_queue', function (Blueprint $table) {
			$table->boolean('batch')->after('tries')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ocr_queue', function (Blueprint $table) {
			$table->dropColumn('batch');
		});
	}

}

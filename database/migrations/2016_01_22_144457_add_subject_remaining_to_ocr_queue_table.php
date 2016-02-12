<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSubjectRemainingToOcrQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ocr_queue', function (Blueprint $table) {
			$table->integer('subject_remaining')->after('subject_count')->default(0);
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
			$table->dropColumn('subject_remaining');
		});
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOcrCsvIdToOcrQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ocr_queue', function (Blueprint $table) {
			$table->unsignedInteger('ocr_csv_id')->after('project_id');
			$table->foreign('ocr_csv_id')->references('id')->on('ocr_csv')->onDelete('cascade');
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
			$table->dropForeign('ocr_queue_ocr_csv_id_foreign');
			$table->dropColumn('ocr_csv_id');
		});
	}

}

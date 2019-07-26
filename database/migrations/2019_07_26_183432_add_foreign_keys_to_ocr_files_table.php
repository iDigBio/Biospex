<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOcrFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ocr_files', function(Blueprint $table)
		{
			$table->foreign('queue_id')->references('id')->on('ocr_queues')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ocr_files', function(Blueprint $table)
		{
			$table->dropForeign('ocr_files_queue_id_foreign');
		});
	}

}

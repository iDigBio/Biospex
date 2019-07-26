<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOcrFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ocr_files', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('queue_id')->unsigned()->index('ocr_files_queue_id_foreign');
			$table->string('subject_id', 191);
			$table->text('messages', 65535)->nullable();
			$table->text('ocr', 65535)->nullable();
			$table->boolean('status')->default(0);
			$table->string('url', 191);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ocr_files');
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOcrQueueTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ocr_queue', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->string('status', 30)->nullable();
			$table->integer('subject_count')->default(0);
			$table->tinyInteger('tries')->default(0);
			$table->tinyInteger('error')->default(0);
			$table->text('attachments')->nullable();
			$table->timestamps();
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->engine = 'InnoDB';
		});

		DB::statement("ALTER TABLE ocr_queue ADD data LONGBLOB AFTER project_id");
		DB::statement("ALTER TABLE ocr_queue ADD uuid BINARY(16) NULL AFTER project_id");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ocr_queue');
	}

}

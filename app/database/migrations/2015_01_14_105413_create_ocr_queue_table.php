<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
			$table->string('status', 30)->nullable();
			$table->tinyInteger('error')->default(0);
			$table->text('message')->nullable();
			$table->timestamps();

			$table->engine = 'InnoDB';
		});

		DB::statement("ALTER TABLE ocr_queue ADD data LONGBLOB AFTER id");
		DB::statement("ALTER TABLE ocr_queue ADD uuid BINARY(16) NULL AFTER id");
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

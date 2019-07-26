<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOcrQueuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ocr_queues', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned()->index('ocr_queues_project_id_foreign');
			$table->integer('expedition_id')->unsigned()->nullable()->index('ocr_queues_expedition_id_foreign');
			$table->integer('total')->default(0);
			$table->integer('processed')->default(0);
			$table->integer('status')->default(0);
			$table->boolean('error')->default(0);
			$table->text('csv', 65535)->nullable();
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
		Schema::drop('ocr_queues');
	}

}

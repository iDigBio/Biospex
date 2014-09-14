<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeadersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('headers', function($table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->text('header')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->engine = 'InnoDB';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('headers');
	}

}

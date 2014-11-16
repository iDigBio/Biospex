<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSubjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		Schema::drop('subjects');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		Schema::create('subjects', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->string('mongo_id');
			$table->timestamps();
			$table->softDeletes();

			$table->engine = 'InnoDB';
		});
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}

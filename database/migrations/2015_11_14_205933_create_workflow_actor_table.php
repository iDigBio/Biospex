<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowActorTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::disableForeignKeys();
		Schema::create('actor_workflow', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('workflow_id');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
			$table->unsignedInteger('actor_id');
			$table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
			$table->integer('order')->default(0);

			$table->engine = 'InnoDB';
		});
        Schema::enableForeignKeys();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('actor_workflow');
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNfnWorkflowsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nfn_workflows', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned()->index('nfn_workflows_project_id_foreign');
			$table->integer('expedition_id')->unsigned()->unique();
			$table->integer('project')->nullable()->index();
			$table->integer('workflow')->nullable()->index();
			$table->text('subject_sets', 65535)->nullable();
			$table->string('slug', 191)->nullable();
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
		Schema::drop('nfn_workflows');
	}

}

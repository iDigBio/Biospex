<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectResourcesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_resources', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned()->index('project_resources_project_id_foreign');
			$table->string('type', 191);
			$table->string('name')->nullable();
			$table->string('description')->nullable();
			$table->string('download_file_name')->nullable();
			$table->integer('download_file_size')->nullable();
			$table->string('download_content_type')->nullable();
			$table->dateTime('download_updated_at')->nullable();
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
		Schema::drop('project_resources');
	}

}

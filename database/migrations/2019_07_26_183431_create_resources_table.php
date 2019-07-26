<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResourcesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('resources', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title')->nullable();
			$table->text('description', 65535)->nullable();
			$table->string('document_file_name', 191)->nullable();
			$table->integer('document_file_size')->nullable();
			$table->string('document_content_type', 191)->nullable();
			$table->dateTime('document_updated_at')->nullable();
			$table->boolean('order')->index();
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
		Schema::drop('resources');
	}

}

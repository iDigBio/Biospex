<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExpeditionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expeditions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->binary('uuid', 16)->nullable();
			$table->integer('project_id')->unsigned()->index('expeditions_project_id_foreign');
			$table->string('title')->nullable();
			$table->text('description', 65535)->nullable();
			$table->string('keywords')->nullable();
			$table->string('logo_file_name', 191)->nullable();
			$table->integer('logo_file_size')->nullable();
			$table->string('logo_content_type', 191)->nullable();
			$table->dateTime('logo_updated_at')->nullable();
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
		Schema::drop('expeditions');
	}

}

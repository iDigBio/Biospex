<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDownloadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('downloads', function(Blueprint $table)
		{
			$table->increments('id');
			$table->binary('uuid', 16)->nullable();
			$table->integer('expedition_id')->unsigned()->index('downloads_expedition_id_foreign');
			$table->integer('actor_id')->unsigned()->index('downloads_actor_id_foreign');
			$table->string('file')->nullable();
			$table->string('type', 191);
			$table->binary('data')->nullable();
			$table->integer('count')->unsigned()->default(0);
			$table->timestamps();
			$table->index(['expedition_id','actor_id','file']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('downloads');
	}

}

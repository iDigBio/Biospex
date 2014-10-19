<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDownloadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('downloads', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expedition_id');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->unsignedInteger('workflow_id');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->text('file');
            $table->unsignedInteger('count');
            $table->timestamps();

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
        Schema::drop('downloads');
	}

}

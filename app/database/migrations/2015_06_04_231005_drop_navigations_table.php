<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropNavigationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::drop('navigations');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::create('navigations', function(Blueprint $table) {
            $table->increments('id');
            $table->string('type', 30);
            $table->string('name', 30);
            $table->string('url');
            $table->string('permission');
            $table->tinyInteger('order');
            $table->integer('parent_id');
            $table->timestamps();

            $table->engine = 'InnoDB';
        });
	}

}

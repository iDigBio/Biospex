<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowsTable2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::disableForeignKeys();
		Schema::create('workflows', function (Blueprint $table) {
			$table->increments('id');
            $table->string('workflow');
			$table->timestamps();
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
        Schema::drop('workflows');
	}

}

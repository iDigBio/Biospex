<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowsTable extends Migration {

    use \DisablesForeignKeys;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->disableForeignKeys();
		Schema::create('workflows', function (Blueprint $table) {
			$table->increments('id');
            $table->string('workflow');
			$table->timestamps();
			$table->engine = 'InnoDB';
		});
		$this->enableForeignKeys();
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

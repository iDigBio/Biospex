<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropProjectActorTable extends Migration {

    use \DisablesForeignKeys;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->disableForeignKeys();
		Schema::drop('project_actor');
		$this->enableForeignKeys();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        $this->disableForeignKeys();

        Schema::create('project_actor', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedInteger('actor_id');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        $this->enableForeignKeys();
	}

}

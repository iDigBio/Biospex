<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExpeditionStatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expedition_stats', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('expedition_id');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->integer('subject_count')->default(0);
            $table->integer('transcriptions_total')->default(0);
            $table->integer('transcriptions_completed')->default(0);
            $table->decimal('percent_completed', 5, 2)->default(0.00);
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
		Schema::drop('expedition_stats');
	}

}

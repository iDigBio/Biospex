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
		Schema::create('expedition_stats', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('expedition_id')->unsigned()->index('expedition_stats_expedition_id_foreign');
			$table->integer('local_subject_count')->default(0);
			$table->integer('subject_count')->default(0);
			$table->integer('transcriptions_total')->default(0);
			$table->integer('transcriptions_completed')->default(0)->index();
			$table->decimal('percent_completed', 5)->default(0.00);
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
		Schema::drop('expedition_stats');
	}

}

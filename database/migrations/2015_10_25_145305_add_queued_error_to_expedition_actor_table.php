<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQueuedErrorToExpeditionActorTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('expedition_actor', function (Blueprint $table) {
			$table->integer('error')->default(0)->after('state');
			$table->integer('queued')->default(0)->after('error');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('expedition_actor', function (Blueprint $table) {
			$table->dropColumn('error');
			$table->dropColumn('queued');
		});
	}

}

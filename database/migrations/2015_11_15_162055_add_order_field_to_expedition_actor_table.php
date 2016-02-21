<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderFieldToExpeditionActorTable extends Migration {

	use \DisablesForeignKeys;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('expedition_actor', function (Blueprint $table) {
			$table->integer('order')->after('completed')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('groups', function (Blueprint $table) {
			$table->dropColumn('order');
		});
	}

}

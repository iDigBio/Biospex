<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFaqsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('faqs', function(Blueprint $table)
		{
			$table->foreign('faq_category_id')->references('id')->on('faq_categories')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('faqs', function(Blueprint $table)
		{
			$table->dropForeign('faqs_faq_category_id_foreign');
		});
	}

}

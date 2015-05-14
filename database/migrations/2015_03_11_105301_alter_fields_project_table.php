<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFieldsProjectTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            $table->renameColumn('website', 'organization_website');
            $table->renameColumn('managed', 'organization');
            $table->renameColumn('hashtag', 'twitter');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            $table->renameColumn('organization_website', 'website');
            $table->renameColumn('organization', 'managed');
            $table->renameColumn('twitter', 'hashtag');
		});
	}

}

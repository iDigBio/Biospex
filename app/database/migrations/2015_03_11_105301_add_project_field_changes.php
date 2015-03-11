<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProjectFieldChanges extends Migration {

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

            $table->string('facebook')->after('keywords');
            $table->string('project_partners')->after('organization');
            $table->string('funding_source')->after('project_partners');


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

            $table->dropColumn('facebook');
            $table->dropColumn('project_partners');
            $table->dropColumn('funding_source');
		});
	}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStateCountiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('state_counties', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('county_name')->nullable();
			$table->string('state_county')->nullable()->index();
			$table->string('state_abbr')->nullable();
			$table->string('state_abbr_cap')->nullable();
			$table->text('geometry', 65535)->nullable();
			$table->string('value')->nullable();
			$table->string('geo_id')->nullable();
			$table->string('geo_id_2')->nullable();
			$table->string('geographic_name')->nullable();
			$table->string('state_num')->nullable()->index('state_num');
			$table->string('county_num')->nullable();
			$table->string('fips_forumla')->nullable();
			$table->string('has_error')->nullable();
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
		Schema::drop('state_counties');
	}

}

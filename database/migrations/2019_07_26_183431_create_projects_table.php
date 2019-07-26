<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->binary('uuid', 16)->nullable();
			$table->integer('group_id')->unsigned()->index();
			$table->string('title')->nullable();
			$table->string('slug')->nullable()->index();
			$table->string('contact')->nullable();
			$table->string('contact_email')->nullable();
			$table->string('contact_title')->nullable();
			$table->string('organization_website')->nullable();
			$table->string('organization')->nullable();
			$table->text('project_partners', 65535)->nullable();
			$table->text('funding_source', 65535)->nullable();
			$table->string('description_short')->nullable();
			$table->text('description_long', 65535)->nullable();
			$table->text('incentives', 65535)->nullable();
			$table->string('geographic_scope')->nullable();
			$table->string('taxonomic_scope')->nullable();
			$table->string('temporal_scope')->nullable();
			$table->string('keywords')->nullable();
			$table->string('blog_url')->nullable();
			$table->string('facebook')->nullable();
			$table->string('twitter')->nullable();
			$table->string('activities')->nullable();
			$table->string('language_skills')->nullable();
			$table->integer('workflow_id')->unsigned()->index('projects_workflow_id_foreign');
			$table->string('logo_file_name')->nullable();
			$table->integer('logo_file_size')->nullable();
			$table->string('logo_content_type')->nullable();
			$table->dateTime('logo_updated_at')->nullable();
			$table->string('banner_file', 191)->nullable();
			$table->text('target_fields', 65535)->nullable();
			$table->enum('status', array('starting','acting','complete','hiatus'))->default('starting');
			$table->binary('advertise')->nullable();
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
		Schema::drop('projects');
	}

}

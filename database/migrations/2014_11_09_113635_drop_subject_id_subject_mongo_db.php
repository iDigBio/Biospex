<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropSubjectIdSubjectMongoDb extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mongodb')->collection('subjectdocs', function(Blueprint $collection)
		{
			$collection->dropIndex('subject_id');
			$collection->dropIndex(['project_id', 'subject_id']);
			$collection->dropSoftDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mongodb')->collection('subjectdocs', function(Blueprint $collection)
		{
			$collection->index('subject_id');
			$collection->unique(['project_id', 'subject_id']);
			$collection->softDeletes();
		});
	}

}

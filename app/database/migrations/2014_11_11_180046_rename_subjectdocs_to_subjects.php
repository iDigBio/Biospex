<?php
use Illuminate\Database\Migrations\Migration;

class RenameSubjectdocsToSubjects extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$mongo = new MongoClient();
		$mongo->admin->command(array('renameCollection'=>'biospex.subjectdocs','to'=>'biospex.subjects'));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$mongo = new MongoClient();
		$mongo->admin->command(array('renameCollection'=>'biospex.subjects','to'=>'biospex.subjectdocs'));
	}

}

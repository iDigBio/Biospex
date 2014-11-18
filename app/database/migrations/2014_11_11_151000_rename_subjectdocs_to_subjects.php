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
		$connection = DB::connection('mongodb');
		$client = $connection->getMongoClient();
		$client->admin->command(array('renameCollection'=>'biospex.subjectdocs','to'=>'biospex.subjects'));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$connection = DB::connection('mongodb');
		$client = $connection->getMongoClient();
		$client->admin->command(array('renameCollection'=>'biospex.subjects','to'=>'biospex.subjectdocs'));
	}

}

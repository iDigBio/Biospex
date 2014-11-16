<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExpeditionSubjectAddProjectIdAlterSubjectId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropIndex('expedition_subject_subject_id_foreign');
			$table->dropForeign('expedition_subject_subject_id_foreign');
			if (Schema::hasColumn('expedition_subject', 'created_at'))
			{
				$table->dropTimestamps();
			}
			if (Schema::hasColumn('expedition_subject', 'deleted_at'))
			{
				$table->dropSoftDeletes();
			}
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->after('id');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
			$table->string('subject_id_tmp')->after('subject_id');
		});


		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$results = DB::select("select * from expedition_subject");
			if ($results)
			{
				DB::update('update expedition_subject es
				inner join subjects s on s.id = es.subject_id
				set es.project_id = s.project_id, es.subject_id_tmp = s.mongo_id');
			}
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropColumn('subject_id');
			$table->renameColumn('subject_id_tmp', 'subject_id');
			$table->index('subject_id');
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropIndex('expedition_subject_project_id_foreign');
			$table->dropForeign('expedition_subject_project_id_foreign');
			$table->dropIndex('expedition_subject_subject_id_index');
			$table->unsignedInteger('subject_id_tmp')->after('expedition_id');
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$results = DB::select('select * from expedition_subject');
			if ($results)
			{
				foreach ($results as $result)
				{
					$values = [
						'project_id' => $result->project_id,
						'mongo_id'   => $result->subject_id,
					];
					$id = DB::table('subjects')->insertGetId($values);
					DB::update("UPDATE expedition_subject set subject_id_tmp = $id WHERE id = {$result->id}");
				}
			}
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
			$table->dropColumn('subject_id');
			$table->renameColumn('subject_id_tmp', 'subject_id');
			$table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
	}

}

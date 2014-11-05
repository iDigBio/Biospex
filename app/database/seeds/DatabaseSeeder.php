<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->call('TruncateTables');
		$this->call('NavigationsTableSeeder');
        $this->call('PermissionTableSeeder');
		$this->call('UserTableSeeder');
		$this->call('GroupTableSeeder');
		$this->call('UserGroupTableSeeder');
		$this->call('ActorsTableSeeder');
        $this->call('ProjectsTableSeeder');
        $this->call('ExpeditionsTableSeeder');
        $this->call('SubjectDocsTableSeeder');
        $this->call('ExpeditionSubjectTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
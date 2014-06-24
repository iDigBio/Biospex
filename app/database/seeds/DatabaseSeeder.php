<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		$this->call('NavigationsTableSeeder');
        $this->call('PermissionTableSeeder');
        $this->call('UserTableSeeder');
        $this->call('GroupTableSeeder');
        $this->call('UserGroupTableSeeder');
        $this->call('ProjectsTableSeeder');
        $this->call('ExpeditionsTableSeeder');

        /*
        $this->call('SubjectsDocsTableSeeder');
        $this->call('SubjectsTableSeeder');
        $this->call('ExpeditionSubjectTableSeeder');
        $this->call('ProjectWorkflowTableSeeder');
        */

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
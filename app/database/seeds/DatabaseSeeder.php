<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Helpers::destroyDir(Config::get('config.dataDir'));

		Eloquent::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		DB::table('properties')->truncate();
		DB::table('headers')->truncate();
		DB::table('metas')->truncate();
		DB::table('subjects')->truncate();
		DB::table('downloads')->truncate();
		DB::table('workflow_manager')->truncate();

		$this->call('NavigationsTableSeeder');
        $this->call('PermissionTableSeeder');
        $this->call('UserTableSeeder');
        $this->call('GroupTableSeeder');
        $this->call('UserGroupTableSeeder');
        $this->call('ProjectsTableSeeder');
        $this->call('ExpeditionsTableSeeder');
        $this->call('ProjectWorkflowTableSeeder');
        $this->call('WorkFlowsTableSeeder');
        $this->call('SubjectDocsTableSeeder');
        $this->call('ExpeditionSubjectTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
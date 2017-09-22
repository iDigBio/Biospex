<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') === 'local')
            echo env('APP_ENV') . PHP_EOL;

        return;


        $this->call('TruncateTables');
        $this->call('PermissionTableSeeder');
        $this->call('ApiSubscribersTableSeeder');
        $this->call('GroupTableSeeder');
        $this->call('UserGroupTableSeeder');
        $this->call('ActorsTableSeeder');
        $this->call('ProjectsTableSeeder');
        $this->call('ExpeditionsTableSeeder');
        $this->call('SubjectsTableSeeder');
        $this->call('ExpeditionSubjectTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

<?php

class DatabaseSeeder extends Seeder
{
    use DisablesForeignKeys;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();

        $this->call('TruncateTables');
        $this->call('PermissionTableSeeder');
        $this->call('UserTableSeeder');
        $this->call('GroupTableSeeder');
        $this->call('UserGroupTableSeeder');
        $this->call('ActorsTableSeeder');
        $this->call('ProjectsTableSeeder');
        $this->call('ExpeditionsTableSeeder');
        $this->call('SubjectsTableSeeder');
        $this->call('ExpeditionSubjectTableSeeder');

        $this->enableForeignKeys();
    }
}

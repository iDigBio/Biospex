<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    protected $permissions;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->permissions = $this->loadData();

        foreach ($this->permissions as $permission) {
            \Biospex\Models\Permission::create($permission);
        }
    }

    public function loadData()
    {
        return [
            // superuser
            [
                'name'        => 'superuser',
                'label'       => 'Superuser',
                'description' => 'Administrator has all permissions'
            ],
            // groups
            [
                'name'        => "create-group",
                'label'       => "Create Group",
                'description' => "Enable group to create group."
            ],
            [
                'name'        => "update-group",
                'label'       => "Update Group",
                'description' => "Enable group to update group."
            ],
            [
                'name'        => "read-group",
                'label'       => "Read Group",
                'description' => "Enable group to read group."
            ],
            [
                'name'        => "delete-group",
                'label'       => "Delete Group",
                'description' => "Enable group to delete group."
            ],
            // project
            [
                'name'        => "create-project",
                'label'       => "Create Project",
                'description' => "Enable group to create project."
            ],
            [
                'name'        => "read-project",
                'label'       => "Read Project",
                'description' => "Enable group to view project."
            ],
            [
                'name'        => "update-project",
                'label'       => "Update Project",
                'description' => "Enable group to update project."
            ],
            [
                'name'        => "delete-project",
                'label'       => "Delete Project",
                'description' => "Enable group to delete project."
            ],
            // expedition
            [
                'name'        => "create-expedition",
                'label'       => "Create Expedition",
                'description' => "Enable group to create expedition."
            ],
            [
                'name'        => "read-expedition",
                'label'       => "Read Expedition",
                'description' => "Enable group to read expedition."
            ],
            [
                'name'        => "update-expedition",
                'label'       => "Update Expedition",
                'description' => "Enable group to update expedition"
            ],
            [
                'name'        => "delete-expedition",
                'label'       => "Delete Expedition",
                'description' => "pages.expedition_delete"
            ],
        ];
    }
}

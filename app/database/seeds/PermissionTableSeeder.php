<?php

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
        Eloquent::unguard();

        $this->permissions = $this->loadData();

        foreach ($this->permissions as $permission) {
            Permission::create($permission);
        }
    }

    public function loadData()
    {
        require_once 'data/permissions.php';

        return $items;
    }
}

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
            App\Models\Permission::create($permission);
        }
    }

    public function loadData()
    {
        require_once 'data/permissions.php';

        return $items;
    }
}

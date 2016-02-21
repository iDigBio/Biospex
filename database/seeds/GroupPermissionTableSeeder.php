<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Permission;

class GroupPermissionTableSeeder extends Seeder
{
    /**
     * @var Group
     */
    private $group;
    /**
     * @var Permission
     */
    private $permission;

    /**
     * @param Group $group
     * @param Permission $permission
     * @internal param User $user
     */
    public function __construct(Group $group, Permission $permission)
    {
        $this->group = $group;
        $this->permission = $permission;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('group_permission')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $groups = $this->group->all();
        $permissions = $this->permission->all();

        foreach ($groups as $group) {
            if ($group->name == 'admins') {
                $group->permissions()->attach($permissions[0]->id);
                unset($permissions[0]);
                continue;
            } else {
                unset($permissions[0]);
                foreach ($permissions as $permission) {
                    $group->permissions()->attach($permission->id);
                }
            }
        }

    }
}
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

        $groups = $this->group->all();
        $permissions = $this->permission->all();

        foreach ($groups as $group) {
            if ($group->name == 'admin') {
                $group->permissions()->attach($permissions[0]->id);
                unset($permissions[0]);
                continue;
            } else {
                foreach ($permissions as $permission) {
                    $group->permissions()->attach($permission->id);
                }
            }
        }

    }
}
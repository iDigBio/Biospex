<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionOrig extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Get permissions by group.
     * @return array
     */
    public static function getPermissionsGroupBy()
    {
        $results = Permission::all();
        $permissions = [];
        foreach ($results as $result) {
            $group = $result['group'];
            if (isset($permissions[$group])) {
                $permissions[$group][] = $result;
            } else {
                $permissions[$group] = [$result];
            }
        }

        return $permissions;
    }

    /**
     * Set the group permissions during create or update
     *
     * @param $data
     * @return array
     */
    public static function setPermissions($data)
    {
        $permissions = [];

        $results = Permission::all();
        foreach ($results as $result) {
            $permissions[$result['name']] = !array_key_exists($result['name'], $data) ? 0 : $data[$result['name']];
        }

        return $permissions;
    }
}

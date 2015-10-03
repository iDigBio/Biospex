<?php

namespace App\Repositories;

use App\Repositories\Contracts\Group;
use App\Models\Group as Model;

class GroupRepository extends Repository implements Group
{
    /**
     * Construct a new Group Object
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Return all the registered groups
     *
     * @param array $columns
     * @return array|mixed
     */
    public function all()
    {
        return \Sentry::findAllGroups();
    }

    /**
     * Return a specific group by a given id
     *
     * @param $id
     * @param array $columns
     * @return bool|\Cartalyst\Sentry\Groups\Group|mixed
     */
    public function find($id)
    {
        return \Sentry::findGroupById($id);
    }

    /**
     * Return all groups
     */
    public function findAllGroups()
    {
        return \Sentry::findAllGroups();
    }

    /**
     * Return a specific group by a given name
     * 
     * @param  string $name
     * @return Group
     */
    public function byName($name)
    {
        return \Sentry::findGroupByName($name);
    }

    /**
     * Return groups with Admins optional and without Users for select options
     *
     * @param $allGroups
     * @param bool $create
     * @return array|mixed
     */
    public function selectOptions($allGroups, $create = false)
    {
        $options = [];
        foreach ($allGroups as $key => $group) {
            if (($group->name == 'Admins' && !$create) || $group->name == 'Users') {
                continue;
            }

            $options[$group->id] = $group->name;
        }

        asort($options);
        return $options;
    }

    /**
     * Find all the groups depending on user
     *
     * @param array $allGroups
     * @return mixed
     */
    public function findAllGroupsWithProjects($allGroups = [])
    {
        return \Sentry::getGroupProvider()->createModel()->findAllGroupsWithProjects($allGroups);
    }
}

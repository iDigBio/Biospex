<?php

namespace App\Repositories\Contracts;

interface Group extends Repository
{
    /**
     * Return a specific user by a given name
     * 
     * @param  string $name
     * @return User
     */
    public function byName($name);

    /**
     * Return groups as list for select options
     *
     * @param $allGroups
     * @param $create
     * @return mixed
     */
    public function selectOptions($allGroups, $create = false);

    /**
     * Find all groups. Using this instead of Sentry all groups due to orderby requirements
     * and the different array structure returned by Sentry when admin or regular user.
     *
     * @param array $allGroups
     * @return mixed
     */
    public function findAllGroupsWithProjects($allGroups = array());

    /**
     * Find all groups
     *
     * @return mixed
     */
    public function findAllGroups();
}

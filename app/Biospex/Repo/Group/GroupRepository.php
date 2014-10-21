<?php namespace Biospex\Repo\Group;
/**
 * GroupRepository.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Biospex\Repo\Repository;
use Biospex\Repo\Permission\PermissionInterface;
use Cartalyst\Sentry\Sentry;
use Group;

class GroupRepository extends Repository implements GroupInterface {

    /**
     * @var \Cartalyst\Sentry\Sentry
     */
    protected $sentry;

    /**
     * @var \Biospex\Repo\Permission\PermissionInterface
     */
    protected $permission;

	/**
	 * Construct a new Group Object
	 */
	public function __construct(
		Group $model,
		Sentry $sentry,
		PermissionInterface $permission
	)
	{
		$this->model = $model;
		$this->sentry = $sentry;
        $this->permission = $permission;
	}

	/**
	 * Return all the registered groups
	 *
	 * @return stdObject Collection of groups
	 */
	public function all($columns = array('*'))
	{
		return $this->sentry->getGroupProvider()->findAll();
	}

	/**
	 * Return a specific group by a given id
	 *
	 * @param  integer $id
	 * @return Group
	 */
	public function find($id, $columns = array('*'))
	{
		try
		{
			$group = $this->sentry->findGroupById($id);
		}
		catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
			return false;
		}
		return $group;
	}

	/**
	 * Return all groups
	 */
	public function findAllGroups ()
	{
		$this->sentry->findAllGroups();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function create($data = array())
	{
		$result = array();
		try {
			    // Create the group
			    $result['group'] = $this->sentry->createGroup(array(
                    'user_id'     => e($data['user_id']),
                    'name'        => e($data['name']),
                    'permissions' => array(),
                ));

			   	$result['success'] = true;
	    		$result['message'] = trans('groups.created');
		}
		catch (\Cartalyst\Sentry\Users\LoginRequiredException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('groups.loginreq');
		}
		catch (\Cartalyst\Sentry\Users\UserExistsException $e)
		{
		    $result['success'] = false;
	    	$result['message'] = trans('groups.userexists');;
		}

		return $result;
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($data = array())
	{
        $permissions = $this->permission->setPermissions($data);

		try
		{
			// Find the group using the group id
		    $group = $this->sentry->findGroupById($data['id']);

		    // Update the group details
		    $group->name = e($data['name']);
		    $group->permissions = $permissions;

		    // Update the group
		    if ($group->save())
		    {
		        // Group information was updated
		        $result['success'] = true;
				$result['message'] = trans('groups.updated');;
		    }
		    else
		    {
		        // Group information was not updated
		        $result['success'] = false;
				$result['message'] = trans('groups.updateproblem');;
		    }
		}
		catch (\Cartalyst\Sentry\Groups\NameRequiredException $e)
		{
			$result['success'] = false;
			$result['message'] = trans('groups.namereq');;
		}
		catch (\Cartalyst\Sentry\Groups\GroupExistsException $e)
		{
			$result['success'] = false;
			$result['message'] = trans('groups.groupexists');;
		}
		catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
			$result['success'] = false;
			$result['message'] = trans('groups.notfound');
		}

		return $result;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		try
		{
		    // Find the group using the group id
		    $group = $this->sentry->findGroupById($id);

		    // Delete the group
		    $group->delete();
		}
		catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
		    return false;
		}
		return true;
	}

	/**
	 * Return a specific group by a given name
	 * 
	 * @param  string $name
	 * @return Group
	 */
	public function byName($name)
	{
		try
		{
		    $group = $this->sentry->findGroupByName($name);
		}
		catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e)
		{
		    return false;
		}
		return $group;
	}

	/**
	 * Return groups with Admins optional and without Users for select options
	 *
	 * @param $allGroups
	 * @param bool $create
	 * @return array|mixed
	 */
	public function selectOptions ($allGroups, $create = false)
    {
		$options = array();
		foreach ($allGroups as $key => $group)
		{
			if (($group->name == 'Admins' && !$create) || $group->name == 'Users')
				continue;

			$options[$group->id] = $group->name;
		}

		asort($options);
		return $options;
    }

    /**
     * Find all the groups depending on user
     *
     * @param $user
     * @param bool $superuser
     * @return mixed|void
     */
	public function findAllGroupsWithProjects ($allGroups = array())
    {
		return $this->sentry->getGroupProvider()->createModel()->findAllGroupsWithProjects($allGroups);
    }
}

<?php namespace Biospex\Repo\Group;

/**
 * CacheGroupDecorator.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
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
use Biospex\Services\Cache\CacheInterface;

class CacheGroupDecorator extends AbstractGroupDecorator {

	protected $cache;
	protected $pass = false;

	/**
	 * Constructor
	 *
	 * @param GroupInterface $group
	 * @param CacheInterface $cache
	 */
	public function __construct (GroupInterface $group, CacheInterface $cache)
	{
		parent::__construct($group);
		$this->cache = $cache;
	}

	/**
	 * All
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function all ($columns = array('*'))
	{
		$key = md5('groups.all');

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$groups = $this->group->all();

		if ( ! $this->pass)
			$this->cache->put($key, $groups);

		return $groups;
	}

	/**
	 * Find
	 *
	 * @param $id
	 * @param array $columns
	 * @return mixed
	 */
	public function find ($id, $columns = array('*'))
	{
		$key = md5("group.$id");

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$group = $this->group->find($id, $columns);

		if ( ! $this->pass)
			$this->cache->put($key, $group);

		return $group;
	}

	/**
	 * Create record
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function create ($data = array())
	{
		$group = $this->group->create($data);
		$this->cache->flush();

		return $group;
	}

	/**
	 * Update record
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function update ($data = array())
	{
		$group = $this->group->update($data);
		$this->cache->flush();

		return $group;
	}

	/**
	 * Destroy records
	 *
	 * @param $id
	 * @return mixed
	 */
	public function destroy ($id)
	{
		$group = $this->group->destroy($id);
		$this->cache->flush();

		return $group;
	}

	/**
	 * Find with eager loading
	 *
	 * @param $id
	 * @param array $with
	 * @return mixed
	 */
	public function findWith ($id, $with = array())
	{
		$key = md5("group.$id." . implode(".", $with));

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$group = $this->group->findWith($id, $with);

		if ( ! $this->pass)
			$this->cache->put($key, $group);

		return $group;
	}

	/**
	 * Return a specific group by a given name
	 *
	 * @param  string $name
	 * @return Group
	 */
	public function byName ($name)
	{
		$key = md5($name);

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$group = $this->group->byName($name);

		if ( ! $this->pass)
			$this->cache->put($key, $group);

		return $group;
	}

	/**
	 * Return groups with Admins optional and without Users for select options
	 *
	 * @param bool $admins
	 * @return mixed
	 */
	public function selectOptions ($allGroups, $create = false)
	{
		/* TODO Figure out a way to cache these per user
		$key = $create ? md5('select-options') : md5('create-options');

		if ($this->cache->has($key))
		{
			return $this->cache->get($key);
		}
		*/

		$options = $this->group->selectOptions($allGroups, $create);

		//$this->cache->put($key, $options);

		return $options;
	}

	/**
	 * Find all groups
	 * @return mixed
	 */
	public function findAllGroups ()
	{
		$key = md5("groups");

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$groups = $this->group->findAllGroups();

		if ( ! $this->pass)
			$this->cache->put($key, $groups);

		return $groups;
	}

	/**
	 * Find all the groups depending on user
	 *
	 * @param array $allGroups
	 * @return mixed
	 */
	public function findAllGroupsWithProjects ($allGroups = array())
	{
		foreach ($allGroups as $group)
		{
			$ids[] = $group->id;
		}
		$key = md5('groups.' . implode(".", $ids));

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$groups = $this->group->findAllGroupsWithProjects($allGroups);

		if ( ! $this->pass)
			$this->cache->put($key, $groups);

		return $groups;
	}

	/**
	 * Save
	 *
	 * @param $record
	 * @return mixed
	 */
	public function save ($record)
	{
		$group = $this->group->save($record);
		$this->cache->flush();

		return $group;
	}

	public function setPass ($value = false)
	{
		$this->pass = $value;
	}
}
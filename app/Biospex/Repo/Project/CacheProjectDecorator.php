<?php namespace Biospex\Repo\Project;

/**
 * CacheProjectDecorator.php
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

class CacheProjectDecorator extends AbstractProjectDecorator
{

	protected $cache;

	/**
	 * Constructor
	 *
	 * @param ProjectInterface $project
	 * @param CacheInterface $cache
	 */
	public function __construct (ProjectInterface $project, CacheInterface $cache)
	{
		parent::__construct($project);
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
		$key = md5('all');

		if ($this->cache->has($key))
		{
			return $this->cache->get($key);
		}

		$projects = $this->project->all();

		$this->cache->put($key, $projects);

		return $projects;
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
		$key = md5('project.' . $id);

		if ($this->cache->has($key))
		{
			return $this->cache->get($key);
		}

		$project = $this->project->find($id, $columns);

		$this->cache->put($key, $project);

		return $project;
	}

	/**
	 * Create record
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function create ($data = array())
	{
		$project = $this->project->create($data);
		$this->cache->flush();

		return $project;
	}

	/**
	 * Update record
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function update ($data = array())
	{
		$project = $this->project->update($data);
		$this->cache->flush();

		return $project;
	}

	/**
	 * Destroy records
	 *
	 * @param $id
	 * @return mixed
	 */
	public function destroy ($id)
	{
		$project = $this->project->destroy($id);
		$this->cache->flush();

		return $project;
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
		$key = md5('project.' . $id . implode(" ", $with));

		if ($this->cache->has($key))
		{
			return $this->cache->get($key);
		}

		$project = $this->project->findWith($id, $with);

		$this->cache->put($key, $project);

		return $project;
	}

	/**
	 * Save
	 *
	 * @param $record
	 * @return mixed
	 */
	public function save ($record)
	{
		$project = $this->project->save($record);
		$this->cache->flush();

		return $project;
	}

	/**
	 * By slug
	 *
	 * @param $slug
	 * @return mixed
	 */
	public function bySlug ($slug)
	{
		$key = md5('slug.' . $slug);

		if ($this->cache->has($key))
		{
			return $this->cache->get($key);
		}

		$project = $this->project->bySlug($slug);

		$this->cache->put($key, $project);

		return $project;
	}

}
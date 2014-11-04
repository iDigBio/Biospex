<?php namespace Biospex\Repo\Expedition;

/**
 * CacheExpeditionDecorator.php
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

class CacheExpeditionDecorator extends AbstractExpeditionDecorator {

	protected $cache;
	protected $pass = false;

	/**
	 * Constructor
	 *
	 * @param ExpeditionInterface $expedition
	 * @param CacheInterface $cache
	 */
	public function __construct (ExpeditionInterface $expedition, CacheInterface $cache)
	{
		parent::__construct($expedition);
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
		$key = md5('expeditions.all');

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		if ( ! $this->pass)
			$expeditions = $this->expedition->all();

		$this->cache->put($key, $expeditions);

		return $expeditions;
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
		$key = md5('expedition.' . $id);

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$expedition = $this->expedition->find($id, $columns);

		if ( ! $this->pass)
			$this->cache->put($key, $expedition);

		return $expedition;
	}

	/**
	 * Create record
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function create ($data = array())
	{
		$expedition = $this->expedition->create($data);
		$this->cache->flush();

		return $expedition;
	}

	/**
	 * Update record
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function update ($data = array())
	{
		$expedition = $this->expedition->update($data);
		$this->cache->flush();

		return $expedition;
	}

	/**
	 * Destroy records
	 *
	 * @param $id
	 * @return mixed
	 */
	public function destroy ($id)
	{
		$expedition = $this->expedition->destroy($id);
		$this->cache->flush();

		return $expedition;
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
		$key = md5('expedition.' . $id . implode(".", $with));

		if ($this->cache->has($key) && ! $this->pass)
		{
			return $this->cache->get($key);
		}

		$expedition = $this->expedition->findWith($id, $with);

		if ( ! $this->pass)
			$this->cache->put($key, $expedition);

		return $expedition;
	}

	/**
	 * Save
	 *
	 * @param $record
	 * @return mixed
	 */
	public function save ($record)
	{
		$expedition = $this->expedition->save($record);
		$this->cache->flush();

		return $expedition;
	}

	/**
	 * Set cache pass
	 *
	 * @param bool $value
	 */
	public function setPass ($value = false)
	{
		$this->pass = $value;
	}
}
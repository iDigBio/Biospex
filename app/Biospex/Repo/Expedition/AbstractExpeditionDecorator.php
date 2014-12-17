<?php namespace Biospex\Repo\Expedition;
/**
 * AbstractExpeditionDecorator.php
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

abstract class AbstractExpeditionDecorator implements ExpeditionInterface
{

	protected $expedition;

	public function __construct (ExpeditionInterface $expedition)
	{
		$this->expedition = $expedition;
	}

	/**
	 * Return all
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function all ($columns = array('*'))
	{
		return $this->expedition->all($columns);
	}

	/**
	 * Find by id
	 *
	 * @param $id
	 * @param array $columns
	 * @return mixed
	 */
	public function find ($id, $columns = array('*'))
	{
		return $this->expedition->find($id, $columns);
	}

	/**
	 * Create record
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function create ($data = array())
	{
		return $this->expedition->create($data);
	}

	/**
	 * Update record
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function update ($data = array())
	{
		return $this->expedition->update($data);
	}

	/**
	 * Destroy records
	 *
	 * @param $id
	 * @return mixed
	 */
	public function destroy ($id)
	{
		return $this->expedition->destroy($id);
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
		return $this->expedition->findWith($id, $with);
	}

	/**
	 * Find by uuid.
	 *
	 * @param $uuid
	 * @return mixed
	 */
	public function findByUuid($uuid)
	{
		return $this->expedition->findByUuid($uuid);
	}
}
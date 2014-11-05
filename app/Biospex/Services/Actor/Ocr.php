<?php  namespace Biospex\Services\Actor;
/**
 * Ocr.php
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
class Ocr extends ActorAbstract {
	/**
	 * @var array
	 */
	protected $states = array();

	/**
	 * Id for the actor
	 * @var null
	 */
	protected $actorId = null;

	/**
	 * Current expedition being processed
	 *
	 * @var
	 */
	protected $record;

	/**
	 * Data Directory
	 *
	 * @var string
	 */
	protected $dataDir;

	/**
	 * Set properties
	 *
	 * @param $actorId
	 * @param bool $debug
	 */
	public function setProperties ($actorId, $debug = false)
	{
		$this->states = [
			'export',
			'getStatus',
			'getResults',
			'completed',
			'analyze',
		];

		$this->setActorId($actorId);
		$this->setReportDebug($debug);

		return;
	}

	/**
	 * Set workflow id
	 *
	 * @param $actorId
	 */
	protected function setActorId ($actorId)
	{
		$this->actorId = $actorId;
	}

	/**
	 * Set debug
	 *
	 * @param bool $debug
	 */
	protected function setReportDebug ($debug = false)
	{
		$this->report->setDebug($debug);
	}

	public function process($id)
	{
		return;
	}

	public function export()
	{
		return;
	}

	public function getStatus()
	{
		return;
	}

	public function getResults()
	{
		return;
	}
}
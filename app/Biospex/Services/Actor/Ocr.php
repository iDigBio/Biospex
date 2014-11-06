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
	 * Actor object
	 */
	protected $actor;

	/**
	 * Expedition Id
	 */
	protected $expeditionId;

	/**
	 * Current expedition being processed
	 *
	 * @var
	 */
	protected $record;

	/**
	 * Set properties
	 *
	 * @param $actor
	 * @param bool $debug
	 */
	public function setProperties ($actor, $debug = false)
	{
		$this->states = [
			'send',
			'completed',
		];

		$this->actor = $actor;
		$this->expeditionId = $actor->pivot->expedition_id;
		$this->report->setDebug($debug);

		return;
	}

	public function process()
	{
		return false;
	}

	public function send()
	{
		return false;
	}

	public function completed()
	{
		return false;
	}
}
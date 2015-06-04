<?php namespace Biospex\Repo\Subject;
/**
 * SubjectRepository.php
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
use Subject;

class SubjectRepository extends Repository implements SubjectInterface {

    /**
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $this->model = $subject;
    }

	public function getUnassignedCount($id)
	{
		return $this->model->getUnassignedCount($id);
	}

	public function getSubjectIds($projectId, $take = null, $expeditionId = null)
	{
		return $this->model->getSubjectIds($projectId, $take, $expeditionId);
	}

	/**
	 * Detach subjects
	 *
	 * @param array $ids
	 * @param $expeditionId
	 * @return mixed
	 */
	public function detachSubjects($ids = [], $expeditionId)
	{
		return $this->model->detachSubjects($ids, $expeditionId);
	}

	/**
	 * Load grid model for jqGrid.
	 */
	public function loadGridModel()
	{
		return $this->model->loadGridModel();
	}

	/**
	 * Grid: get total number of rows.
	 *
	 * @param array $filters
	 * @return int
	 */
	public function getTotalNumberOfRows(array $filters = [])
	{
		return $this->model->getTotalNumberOfRows($filters);
	}

	/**
	 * Grid: get rows.
	 *
	 * @param $limit
	 * @param $offset
	 * @param null $orderBy
	 * @param null $sord
	 * @param bool $initial
	 * @param array $filters
	 * @return array
	 */
	public function getRows($limit, $offset, $orderBy = null, $sord = null, array $filters = [])
	{
		return $this->model->getRows($limit, $offset, $orderBy, $sord, $filters);
	}

    /**
     * @param $filename
     * @return mixed
     */
    public function findByFilename($filename)
    {
        return $this->model->findByFilename($filename);
    }
}
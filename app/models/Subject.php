<?php
/**
 * Subject.php
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

use Jenssegers\Mongodb\Model as Eloquent;

class Subject extends Eloquent {

	/**
	 * Redefine connection to use mongodb
	 */
	protected $connection = 'mongodb';

	/**
	 * Set collection
	 */
	protected $collection = 'subjects';

	/**
	 * Set primary key
	 */
	protected $primaryKey = '_id';

	/**
	 * set guarded properties
	 */
	protected $guarded = array('_id');

	/**
	 * Visible columns
	 *
	 * @var Array
	 *
	 */
	protected $visibleColumns = ['id', 'accessURI', 'ocr'];

	/**
	 * OrderBy
	 *
	 * @var array
	 *
	 */
	protected $orderBy = array(array());

	/**
	 * Finds document by unique object id (from media.csv)
	 *
	 * @param $value
	 * @return mixed
	 */
	public function findById ($value)
	{
		return $this->where('id', $value)->get();
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function project ()
	{
		return $this->belongsTo('Project');
	}

	public function scopeProjectId ($query, $id)
	{
		return $query->where('project_id', $id);
	}

	/**
	 * Return count of project subjects not assigned to expeditions
	 * @param $projectId
	 * @return mixed
	 */
	public function getUnassignedCount ($projectId)
	{
		return $this->where('expedition_ids', 'size', 0)
			->where('project_id', $projectId)
			->count();
	}

	/**
	 * Return subjects by id. Use for assigned and unassigned.
	 * @param $projectId
	 * @param $take
	 * @param $expeditionId
	 * @return array
	 */
	public function getSubjectIds ($projectId, $take, $expeditionId)
	{
		$ids = $this->whereNested(function ($query) use ($projectId, $take, $expeditionId)
		{
			if ( ! is_null($expeditionId))
			{
				$query->where('expedition_ids', '=', $expeditionId);
			} else
			{
				$query->where('expedition_ids', 'size', 0);
			}

			$query->where('project_id', '=', "$projectId");
		})
			->take($take)
			->get(array('_id'))
			->toArray();

		return array_flatten($ids);
	}

	/**
	 * Find by foreign id.
	 *
	 * @param $column
	 * @param $id
	 * @return mixed
	 */
	public function findByForeignId ($column, $id)
	{
		return $this->where($column, $id)->first();
	}

	/**
	 * Hokey Pokey way to detach mongodb subjects from expeditions.
	 *
	 * @param $ids
	 * @param $expeditionId
	 */
	public function detachSubjects ($ids, $expeditionId)
	{
		foreach ($ids as $id)
		{
			$array = [];
			$subject = $this->find($id);
			foreach ($subject->expedition_ids as $value)
			{
				if ($expeditionId != $value)
					$array[] = $value;
			}
			$subject->expedition_ids = $array;
			$subject->save();
		}

		return;
	}

	/**
	 * Calculate the number of rows. It's used for paging the result.
	 *
	 * @param  array $filters
	 *  An array of filters, example: array(array('field'=>'column index/name 1','op'=>'operator','data'=>'searched string column 1'), array('field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2'))
	 *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
	 *  The 'op' key will contain one of the following operators: '=', '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
	 *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
	 *  The 'data' key will contain the string searched by the user.
	 * @return integer
	 *  Total number of rows
	 */
	public function getTotalNumberOfRows ($filters)
	{
		return intval($this->whereNested(function ($query) use ($filters)
		{
			$query->where('project_id', '=', Route::input('projects'));

			foreach ($filters as $filter)
			{
				if ($filter['op'] == 'is in')
				{
					$query->whereIn($filter['field'], explode(',', $filter['data']));
					continue;
				}

				if ($filter['op'] == 'is not in')
				{
					$query->whereNotIn($filter['field'], explode(',', $filter['data']));
					continue;
				}

				$query->where($filter['field'], $filter['op'], $filter['data']);
			}
		})
			->count());
	}


	/**
	 * Get the rows data to be shown in the grid.
	 *
	 * @param  integer $limit
	 *  Number of rows to be shown into the grid
	 * @param  integer $offset
	 *  Start position
	 * @param  string $orderBy
	 *  Column name to order by.
	 * @param  array $sordvisibleColumns
	 *  Sorting order
	 * @param  array $filters
	 *  An array of filters, example: array(array('field'=>'column index/name 1','op'=>'operator','data'=>'searched string column 1'), array('field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2'))
	 *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
	 *  The 'op' key will contain one of the following operators: '=', '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
	 *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
	 *  The 'data' key will contain the string searched by the user.
	 * @return array
	 *  An array of array, each array will have the data of a row.
	 *  Example: array(array('row 1 col 1','row 1 col 2'), array('row 2 col 1','row 2 col 2'))
	 */
	public function getRows ($limit, $offset, $orderBy, $sord, $filters)
	{
		if ( ! is_null($orderBy) || ! is_null($sord))
		{
			$this->orderBy = array(array($orderBy, $sord));
		}

		if ($limit == 0)
		{
			$limit = 1;
		}

		$orderByRaw = array();

		foreach ($this->orderBy as $orderBy)
		{
			array_push($orderByRaw, implode(':', $orderBy));
		}

		$orderByRaw = implode(',', $orderByRaw);

		$rows = $this->whereNested(function ($query) use ($filters)
		{
			$query->where('project_id', '=', Route::input('projects'));

			foreach ($filters as $filter)
			{
				if ($filter['op'] == 'is in')
				{
					$query->whereIn($filter['field'], explode(',', $filter['data']));
					continue;
				}

				if ($filter['op'] == 'is not in')
				{
					$query->whereNotIn($filter['field'], explode(',', $filter['data']));
					continue;
				}

				$query->where($filter['field'], $filter['op'], $filter['data']);
			}
		})
			->take($limit)
			->skip($offset)
			->orderBy($orderByRaw)
			->get($this->visibleColumns);

		if ( ! is_array($rows))
		{
			$rows = $rows->toArray();
		}

		foreach ($rows as &$row)
		{
			$row = array_values((array) $row);
		}

		return $rows;
	}
}
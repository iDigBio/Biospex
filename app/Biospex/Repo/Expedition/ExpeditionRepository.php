<?php namespace Biospex\Repo\Expedition;
/**
 * ExpeditionRepository.php
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
use Expedition;
use Biospex\Repo\Repository;
use Mgallegos\LaravelJqgrid\Repositories\RepositoryInterface;
use \Illuminate\Support\Facades\DB;

class ExpeditionRepository extends Repository implements ExpeditionInterface, RepositoryInterface {

    /**
     * @var \Expedition
     */
    protected $model;

    /**
     * Database
     */
    protected $Database;

    /**
     * Visible columns
     *
     * @var Array
     *
     */
    protected $visibleColumns;

    /**
     * OrderBy
     *
     * @var array
     *
     */
    protected $orderBy = array(array());


    /**
     * @param Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {
        $this->model = $expedition;
        $this->Database = DB::table('expeditions')
            ->join('expedition_subject', 'expeditions.id', '=', 'expedition_subject.expedition_id')
            ->join('subjects', 'expeditions_subject.subject_id', '=', 'subjects.id');

        $this->visibleColumns = array();

        $this->orderBy = array(array('subjects.object_id', 'asc'));
    }

    /**
     * Override parent create to allow sync
     *
     * @param array $data
     * @return mixed|void
     */
    public function create(array $data)
    {
        $expedition = parent::create($data);
        $expedition->subject()->sync($data['subject_ids']);
        return $expedition;
    }

    /**
     * Find by project id
     *
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findByProjectId($projectId)
    {
        return $this->model->findByProjectId($projectId);
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
    public function getTotalNumberOfRows(array $filters = array())
    {
        return  intval($this->Database->whereNested(function($query) use ($filters)
        {
            foreach ($filters as $filter)
            {
                if($filter['op'] == 'is in')
                {
                    $query->whereIn($filter['field'], explode(',',$filter['data']));
                    continue;
                }

                if($filter['op'] == 'is not in')
                {
                    $query->whereNotIn($filter['field'], explode(',',$filter['data']));
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
    public function getRows($limit, $offset, $orderBy = null, $sord = null, array $filters = array())
    {
        if(!is_null($orderBy) || !is_null($sord))
        {
            $this->orderBy = array(array($orderBy, $sord));
        }

        if($limit == 0)
        {
            $limit = 1;
        }

        $orderByRaw = array();

        foreach ($this->orderBy as $orderBy)
        {
            array_push($orderByRaw, implode(' ',$orderBy));
        }

        $orderByRaw = implode(',',$orderByRaw);

        $rows = $this->Database->whereNested(function($query) use ($filters)
        {
            foreach ($filters as $filter)
            {
                if($filter['op'] == 'is in')
                {
                    $query->whereIn($filter['field'], explode(',',$filter['data']));
                    continue;
                }

                if($filter['op'] == 'is not in')
                {
                    $query->whereNotIn($filter['field'], explode(',',$filter['data']));
                    continue;
                }

                $query->where($filter['field'], $filter['op'], $filter['data']);
            }
        })
            ->take($limit)
            ->skip($offset)
            ->orderByRaw($orderByRaw)
            ->get($this->visibleColumns);

        if(!is_array($rows))
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
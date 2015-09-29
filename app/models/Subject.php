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
use Cviebrock\EloquentTypecast\EloquentTypecastTrait;

class Subject extends Eloquent
{
    use EloquentTypecastTrait;

    protected $castOnSet = true;

    protected $cast = array(
        'project_id' => 'integer',
        'expedition_ids' => 'integer',
    );

    /**
     * Redefine connection to use mongodb
     */
    protected $connection = 'mongodb';

    /**
     * Set primary key
     */
    protected $primaryKey = '_id';

    /**
     * set guarded properties
     */
    protected $guarded = ['_id'];

    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Project');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\EmbedsMany
     */
    public function occurrence()
    {
        return $this->embedsOne('Occurrence', 'occurrence');
    }

    /**
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeProjectId($query, $id)
    {
        return $query->where('project_id', (int) $id);
    }

    /**
     * Finds document by unique object id (from media.csv)
     *
     * @param $value
     * @return mixed
     */
    public function findById($value)
    {
        return $this->where('id', $value)->get();
    }

    /**
     * Find by project id and occurrence id.
     *
     * @param $project_id
     * @param $occurrence_id
     * @return mixed
     */
    public function findByProjectOccurrenceId($project_id, $occurrence_id)
    {
        return $this->projectid($project_id)->where('occurrence.id', $occurrence_id)->get();
    }

    /**
     * Return count of project subjects not assigned to expeditions
     *
     * @param $projectId
     * @return mixed
     */
    public function getUnassignedCount($projectId)
    {
        return $this->where('expedition_ids', 'size', 0)
            ->where('project_id', (int) $projectId)
            ->count();
    }

    /**
     * Return subjects by id. Use for assigned and unassigned.
     *
     * @param $projectId
     * @param $take
     * @param $expeditionId
     * @return array
     */
    public function getSubjectIds($projectId, $take, $expeditionId)
    {
        $ids = $this->whereNested(function ($query) use ($projectId, $take, $expeditionId) {
            if (! is_null($expeditionId)) {
                $query->where('expedition_ids', '=', (int) $expeditionId);
            } else {
                $query->where('expedition_ids', 'size', 0);
            }

            $query->where('project_id', '=', (int) $projectId);
        })
            ->take($take)
            ->get(['_id'])
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
    public function findByForeignId($column, $id)
    {
        return $this->where($column, $id)->first();
    }

    /**
     * Hokey Pokey way to detach mongodb subjects from expeditions.
     *
     * @param $ids
     * @param $expeditionId
     */
    public function detachSubjects($ids, $expeditionId)
    {
        foreach ($ids as $id) {
            $array = [];
            $subject = $this->find($id);
            foreach ($subject->expedition_ids as $value) {
                if ((int) $expeditionId != $value) {
                    $array[] = $value;
                }
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
    public function getTotalNumberOfRows($filters)
    {
        return intval($this->whereNested(function ($query) use ($filters) {
            $this->buildQuery($query, $filters);
        })->count());
    }


    /**
     *
     * Get the rows data to be shown in the grid.
     *
     * @param $limit
     * @param $offset
     * @param $orderBy
     * @param $sord
     * @param $filters
     * @return array
     *  An array of filters, example: array(array('field'=>'column index/name 1','op'=>'operator','data'=>'searched string column 1'), array('field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2'))
     *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
     *  The 'op' key will contain one of the following operators: '=', '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
     *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
     *  The 'data' key will contain the string searched by the user.
     * @return array
     *  An array of array, each array will have the data of a row.
     *  Example: array(array('row 1 col 1','row 1 col 2'), array('row 2 col 1','row 2 col 2'))
     */
    public function getRows($limit, $offset, $orderBy, $sord, $filters)
    {
        $selectColumns = Config::get('config.selectColumns');

        if (! is_null($orderBy) || ! is_null($sord)) {
            $this->orderBy = [[$orderBy, $sord]];
        }

        if ($limit == 0) {
            $limit = 1;
        }

        $orderByRaw = [];

        foreach ($this->orderBy as $orderBy) {
            array_push($orderByRaw, implode(':', $orderBy));
        }

        $orderByRaw = implode(',', $orderByRaw);

        $rows = $this->whereNested(function ($query) use ($filters) {
            $this->buildQuery($query, $filters);
        })
            ->take($limit)
            ->skip($offset)
            ->orderBy($orderByRaw)
            ->get($selectColumns);

        if (! is_array($rows)) {
            $rows = $rows->toArray();
        }

        $this->setRowCheckbox($rows);

        return $rows;
    }

    /**
     * Retrieve subject via accessURI filename (extensions not included).
     *
     * @param $filename
     * @return mixed
     */
    public function findByFilename($filename)
    {
        return $this->where('accessURI', 'like', '%' . $filename . '%')->first();
    }

    /**
     * Build query for search.
     *
     * @param $query
     * @param $filters
     */
    protected function buildQuery(&$query, $filters)
    {
        $projectId = (int) Route::input('projects');
        $expeditionId = (int) Route::input('expeditions');

        $query->where('project_id', '=', $projectId);

        foreach ($filters as $filter) {
            if ($filter['field'] == 'expedition_ids') {
                $this->expeditionIdFilter($filter, $query, $expeditionId);
                continue;
            }

            if ($filter['op'] == 'is in') {
                $query->whereIn($filter['field'], explode(',', $filter['data']));
                continue;
            }

            if ($filter['op'] == 'is not in') {
                $query->whereNotIn($filter['field'], explode(',', $filter['data']));
                continue;
            }

            $query->where($filter['field'], $filter['op'], $filter['data']);
        }

        return;
    }

    /**
     * Filter for expedition id present or not.
     *
     * @param $filter
     * @param $query
     * @param $expeditionId
     */
    protected function expeditionIdFilter($filter, &$query, $expeditionId)
    {
        if ($filter['data'] == "true") {
            if (empty($expeditionId)) {
                $query->whereRaw(['expedition_ids' => ['$not' => ['$size' => 0]]]);

                return;
            }

            $query->whereIn($filter['field'], [(int) $expeditionId]);

            return;
        }

        if (empty($expeditionId)) {
            $query->where('expedition_ids', 'size', 0);

            return;
        }

        $query->whereNotIn($filter['field'], [(int) $expeditionId]);

        return;
    }


    /**
     * If row has expeditionId, mark as checked
     *
     * @param $rows
     */
    protected function setRowCheckbox(&$rows)
    {
        $expeditionId = (int) Route::input('expeditions');
        foreach ($rows as &$row) {
            if (empty($expeditionId)) {
                $row['expedition_ids'] = ! empty($row['expedition_ids']) ? "Yes" : "No";
                continue;
            }

            //$row['checked'] = in_array($expeditionId, $row['expedition_ids']) ? true : false;
            $row['expedition_ids'] = in_array($expeditionId, $row['expedition_ids']) ? "Yes" : "No";
        }
    }

    public function loadGridModel()
    {
        $colNames = Config::get('config.modelColumns');
        $colModel = $this->setColModel();

        return ['colNames' => $colNames, 'colModel' => $colModel];
    }

    /**
     * Build column model for grid.
     *
     * @return array
     */
    protected function setColModel()
    {
        $selectColumns = Config::get('config.selectColumns');

        foreach ($selectColumns as $column) {
            $colModel[] = $this->formatColumn($column);
        }

        return $colModel;
    }

    /**
     * Format the given column for grid model.
     *
     * @param $column
     * @return array
     */
    protected function formatColumn($column)
    {
        if ($column == 'expedition_ids') {
            return $this->buildExpeditionCheckbox();
        }

        $col = [
            'name'      => $column,
            'index'     => $column,
            'key'       => false,
            'resizable' => true,
            'search'    => true,
            'sortable'  => true,
            'editable'  => false
        ];

        if ($column == 'ocr') {
            $col = array_merge($col, [
                'title'   => false,
                'classes' => "ocrPreview"
            ]);
        }

        if ($column == 'accessURI') {
            $this->addUriLink($col);
        }

        return $col;
    }

    protected function addUriLink(&$col)
    {
        $col = array_merge($col, [
            'classes'   => "thumbPreview",
            'formatter' => 'imagePreview'
        ]);
    }

    protected function buildExpeditionCheckbox()
    {
        return [
            'name'          => 'expedition_ids',
            'index'         => 'expedition_ids',
            'width'         => 100,
            'align'         => 'center',
            //'formatter' => 'checkbox',
            //'edittype' => 'checkbox',
            //'editoptions' => ['value' => 'Yes:No', 'defaultValue' => 'No'],
            'stype'         => 'select',
            'searchoptions' => ['sopt' => ['eq', 'ne'], 'value' => ':Any;true:Yes;false:No']
        ];
    }
}

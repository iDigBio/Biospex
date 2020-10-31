<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories\MongoDb;

use App\Models\Subject as Model;
use App\Repositories\Interfaces\Subject;

class SubjectRepository extends MongoDbRepository implements Subject
{
    /**
     * OrderBy
     *
     * @var array
     */
    protected $orderBy = [[]];

    /**
     * Group op value: AND/OR = true/false
     * @var
     */
    protected $groupAnd;

    /**
     * Sets whether first filter created (where vs orWhere)
     * @var bool
     */
    protected $groupOpProcessed = false;

    /**
     * @var
     */
    protected $projectId;

    /**
     * @var
     */
    protected $expeditionId;

    /**
     * @var
     */
    protected $route;

    /**
     * @var
     */
    protected $assignedRuleData;

    /**
     * Specify Model class name
     *
     * @return \App\Models\Subject|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function findSubjectsByExpeditionId($expeditionId, array $attributes = ['*'])
    {
        return \Cache::tags((string) $expeditionId)->remember(__METHOD__, 14440, function () use ($expeditionId, $attributes) {
            return $this->model->where('expedition_ids', $expeditionId)->get($attributes);
        });
    }

    /**
     * @inheritdoc
     */
    public function getUnassignedCount($projectId)
    {
        return $this->model->where('expedition_ids', 'size', 0)
            ->where('project_id', (int) $projectId)
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function getSubjectAssignedCount($projectId)
    {
        return $this->model
            ->whereRaw(['expedition_ids.0' => ['$exists' => true]])
            ->where('project_id', '=', (int) $projectId)
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function detachSubjects($subjectIds, $expeditionId)
    {
        $subjectIds->each(function($subjectId) use($expeditionId){
            $subject = $this->model->find($subjectId);
            $subject->expedition_ids = collect($subject->expedition_ids)->diff($expeditionId)->toArray();
            $subject->save();
        });

        event('cache.flush', $expeditionId);
    }

    /**
     * @inheritDoc
     */
    public function attachSubjects($subjectIds, $expeditionId)
    {
        $subjectIds->each(function($subjectId) use($expeditionId){
            $subject = $this->model->find($subjectId);
            $subject->expedition_ids = collect($subject->expedition_ids)->push($expeditionId)->toArray();
            $subject->save();
        });

        event('cache.flush', $expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function findByAccessUri($accessURI)
    {
        return $this->model->where('accessURI', 'like', '%' . $accessURI . '%')->first();
    }

    /**
     * @inheritDoc
     */
    public function deleteUnassignedSubjects($projectId)
    {
        $this->model->where('project_id', (int) $projectId)
            ->where('expedition_ids', 'size', 0)
            ->delete();
    }

    /**
     * Calculate the number of rows. It's used for paging the result.
     *
     * @param array $vars
     * page
     * limit
     * count
     * sidx
     * sord
     * filters
     * projectId
     * expeditionId
     *
     *  Filters is an array: example: array(array('field'=>'column index/name 1','op'=>'operator','data'=>'searched string column 1'), array('field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2'))
     *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
     *  The 'op' key will contain one of the following operators: '=', '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
     *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
     *  The 'data' key will contain the string searched by the user.
     *
     * @throws \Exception
     * @return int Total number of rows
     */
    public function getTotalRowCount(array $vars = [])
    {
        $count = $this->model->whereNested(function ($query) use ($vars)
        {
            $this->buildQuery($query, $vars);
        })->count();

        return (int) $count;
    }

    /**
     *
     * Get the rows data to be shown in the grid.
     *
     * @param $vars
     *  An array of filters, example: array(array('field'=>'column index/name 1','op'=>'operator','data'=>'searched string column 1'), array('field'=>'column index/name 2','op'=>'operator','data'=>'searched string column 2'))
     *  The 'field' key will contain the 'index' column property if is set, otherwise the 'name' column property.
     *  The 'op' key will contain one of the following operators: '=', '<', '>', '<=', '>=', '<>', '!=','like', 'not like', 'is in', 'is not in'.
     *  when the 'operator' is 'like' the 'data' already contains the '%' character in the appropiate position.
     *  The 'data' key will contain the string searched by the user.
     * @return array
     *  An array of array, each array will have the data of a row.
     *  Example: array(array('row 1 col 1','row 1 col 2'), array('row 2 col 1','row 2 col 2'))
     *
     * $limit, $start, $sidx, $sord, $filters
     * @throws \Exception
     */
    public function getRows(array $vars = [])
    {
        $orderByRaw = $this->setOrderBy($vars['sidx'], $vars['sord']);

        $vars['limit'] = ($vars['limit'] === 0) ? 1 : $vars['limit'];

        $query = $this->model->whereNested(function ($query) use ($vars)
        {
            $this->buildQuery($query, $vars);
        })
            ->take($vars['limit'])
            ->skip($vars['offset']);

        foreach ($orderByRaw as $field => $sort)
        {
            $query->orderBy($field, $sort);
        }

        $rows = $query->get();


        if ( ! is_array($rows))
        {
            $rows = $rows->toArray();
        }

        $this->setRowCheckbox($rows);

        return $rows;
    }

    /**
     * Build query for search.
     *
     * @param $query
     * @param $vars
     */
    protected function buildQuery(&$query, $vars)
    {
        $this->setGroupOp($vars['filters']);

        $this->setWhere($query, 'project_id', '=', $vars['projectId']);

        if (isset($vars['filters']['rules']) && is_array($vars['filters']['rules']))
        {
            $rules = $vars['filters']['rules'];
            $query->where(function ($query) use ($rules)
            {
                $this->handleRules($query, $rules);
            });
        }

        if ($vars['route'] !== 'admin.grids.explore') {
            $this->setExpeditionWhere($query, $vars);
        }
    }

    protected function setExpeditionWhere(&$query, $vars)
    {
        // admin.grids.explore: Project Explore (show all)
        // admin.grids.show: Expedition Show page (show only assigned)
        // admin.grids.edit: Expedition edit (show all)
        // admin.grids.create: Expedition create (show not assigned)
        if ($vars['route'] === 'admin.grids.edit')
        {
            if ($this->assignedRuleData === '' || $this->assignedRuleData === 'all')
                return;
        }
        elseif ($vars['route'] === 'admin.grids.show')
        {
            $this->setWhereIn($query, 'expedition_ids', [$vars['expeditionId']]);
        }
        elseif ($vars['route'] === 'admin.grids.create')
        {
            $this->setWhere($query, 'expedition_ids', 'size', 0);
        }
    }

    /**
     * Handle the passed filters.
     * @param $query
     * @param $rules
     */
    protected function handleRules(&$query, $rules)
    {
        foreach ($rules as $rule)
        {
            if ($rule['field'] === 'assigned')
            {
                $this->assignedRule($query, $rule);
                continue;
            }

            if ($rule['field'] === 'expedition_ids') {
                $rule['data'] = (int) $rule['data'];
            }

            if ($rule['field'] === 'transcribed')
            {
                if ($rule['data'] === 'all') {
                    continue;
                }
                $rule['data'] = $rule['data'] === 'true' ? (bool) true : (bool) false;
            }

            $this->buildWhere($query, $rule);
        }
    }

    /**
     * Build where clause for query.
     * eq: equal
     * ne: not equal
     * bw: begins with
     * bn: does not begin with
     * ew: ends with
     * en: does not end with
     * cn: contains
     * nc: does not contains
     * nu: is null
     * nn: is not null
     * lt: less than
     * le: less or equal
     * gt: greater
     * ge: greater or equal
     * in: is in
     * ni: is not in
     *
     * @param $rule
     * @param $query
     */
    protected function buildWhere(&$query, $rule)
    {
        switch ($rule['op'])
        {
            case 'bw':
                $this->setWhere($query, $rule['field'], 'regexp', '/^' . $rule['data'] . '/i');
                break;
            case 'bn':
                $this->setWhere($query, $rule['field'], 'regexp', '/^(?!' . $rule['data'] . ').+/i');
                break;
            case 'ew':
                $this->setWhere($query, $rule['field'], 'regexp', '/^(?!' . '/' . $rule['data'] . '$/i');
                break;
            case 'en':
                $this->setWhere($query, $rule['field'], 'regexp', '/.*(?<!' . $rule['data'] . ')$/i');
                break;
            case 'cn':
                $this->setWhere($query, $rule['field'], 'like', '%' . $rule['data'] . '%');
                break;
            case 'nc':
                $this->setWhere($query, $rule['field'], 'not regexp', '/' . $rule['data'] . '/i');
                break;
            case 'nu':
                $this->setWhereNull($query, $rule['field']);
                break;
            case 'nn':
                $this->setWhereNotNull($query, $rule['field']);
                break;
            case 'in':
                $this->setWhereIn($query, $rule['field'], explode(',', $rule['data']));
                break;
            case 'ni':
                $this->setWhereNotIn($query, $rule['field'], explode(',', $rule['data']));
                break;
            case 'eq':
                $this->setWhere($query, $rule['field'], '=', $rule['data']);
                break;
            case 'ne':
                $this->setWhere($query, $rule['field'], '!=', $rule['data']);
                break;
            case 'lt':
                $this->setWhere($query, $rule['field'], '<', $rule['data']);
                break;
            case 'le':
                $this->setWhere($query, $rule['field'], '<=', $rule['data']);
                break;
            case 'gt':
                $this->setWhere($query, $rule['field'], '>', $rule['data']);
                break;
            case 'ge':
                $this->setWhere($query, $rule['field'], '>=', $rule['data']);
                break;
        }

    }

    /**
     * Filter for if subject is assigned to an expedition.
     * data = all, true, false
     * @param $rule
     * @param $query
     *
     */
    protected function assignedRule(&$query, $rule)
    {
        $this->assignedRuleData = $rule['data'];

        if ($rule['data'] === 'all')
        {
            return;
        }

        $this->setWhereForAssigned($query, $rule);
    }

    /**
     * @param $query
     * @param $rule
     */
    protected function setWhereForAssigned(&$query, $rule)
    {
        if ($rule['data'] === 'true')
        {
            $this->setWhereRaw($query, $rule['field'], ['$not' => ['$size' => 0]]);
        }
        else
        {
            $this->setWhere($query, $rule['field'], 'size', 0);
        }
    }

    /**
     * @param $orderBy
     * @param $sord
     * @return array
     */
    public function setOrderBy($orderBy, $sord)
    {
        $orderByRaw = [];
        if ($orderBy !== null)
        {
            $orderBys = explode(',', $orderBy);
            foreach ($orderBys as $order)
            {
                $order = trim($order);
                [$field, $sort] = array_pad(explode(' ', $order, 2), 2, $sord);
                $orderByRaw [trim($field)] = trim($sort);
            }
        }

        return $orderByRaw;
    }

    /**
     * If row has expeditionId, mark as checked
     *
     * @param $rows
     */
    protected function setRowCheckbox(&$rows)
    {
        foreach ($rows as &$row)
        {
            $row['assigned'] =! empty($row['expedition_ids']) ? 'Yes' : 'No';
            //$row['expedition_ids'] = ! empty($row['expedition_ids']) ? 'Yes' : 'No';
        }
    }

    /**
     * Set group operator.
     * @param $filters
     * @internal param $groupOp
     */
    protected function setGroupOp($filters)
    {
        $this->groupAnd = true;

        if (isset($filters['groupOp']))
        {
            $this->groupAnd = ($filters['groupOp'] === 'AND');
        }
    }

    /**
     * Set groupOp process
     * @param $bool
     */
    protected function setGroupOpProcessed($bool = false)
    {
        $this->groupOpProcessed = $bool;
    }

    /**
     * Set where/orWhere clause for query
     * @param $query
     * @param $field
     * @param $operation
     * @param $data
     */
    protected function setWhere(&$query, $field, $operation, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->where($field, $operation, $data) : $query->orWhere($field, $operation, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereRaw/orWhereRaw for query
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereRaw(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereRaw([$field => $data]) : $query->orWhereRaw([$field => $data]);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereIn/orWhereIn for query
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereIn(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereIn($field, $data) : $query->orWhereIn($field, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereIn/orWhereIn for query
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereNotIn(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereNotIn($field, $data) : $query->orWhereNotIn($field, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereNull/orWhereNull for query
     * @param $query
     * @param $field
     */
    protected function setWhereNull(&$query, $field)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereNull($field) : $query->orWhereNull($field);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereNotNull/orWhereNotNull for query
     * @param $query
     * @param $field
     */
    protected function setWhereNotNull(&$query, $field)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereNotNull($field) : $query->orWhereNotNull($field);

        $this->setGroupOpProcessed(true);
    }
}
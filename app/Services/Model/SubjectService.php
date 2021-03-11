<?php
/*
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

namespace App\Services\Model;

use App\Models\Subject;
use Illuminate\Support\LazyCollection;

/**
 * Class SubjectService
 *
 * @package App\Services\Model
 */
class SubjectService extends BaseModelService
{
    /**
     * @var 
     */
    private $groupAnd;

    /**
     * @var 
     */
    private $groupOpProcessed;

    /**
     * @var mixed
     */
    private $assignedRuleData;

    /**
     * SubjectService constructor.
     *
     * @param \App\Models\Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $this->model = $subject;
    }

    /**
     * Get subjects by expedition id and return using lazycollection.
     *
     * @param $expeditionId
     * @return \Illuminate\Support\LazyCollection
     */
    public function getAssignedByExpeditionId($expeditionId): LazyCollection
    {
        return $this->model->where('expedition_ids', $expeditionId)->cursor();
    }

    /**
     * Find by expedition id.
     *
     * @param $expeditionId
     * @param array|string[] $attributes
     * @return array|mixed
     */
    public function findByExpeditionId($expeditionId, array $attributes = ['*'])
    {
        return \Cache::tags((string) $expeditionId)->remember(__METHOD__, 14440, function () use (
            $expeditionId,
            $attributes
        ) {
            return $this->model->where('expedition_ids', $expeditionId)->get($attributes);
        });
    }

    /**
     * Return query for processing subjects in ocr.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return \App\Models\Subject|\Illuminate\Database\Eloquent\Builder
     */
    public function getSubjectQueryForOcr(int $projectId, int $expeditionId = null)
    {
        $query = $this->model->where('project_id', $projectId);
        $query = null === $expeditionId ? $query : $query->where('expedition_id', $expeditionId);
        $query = $query->where('ocr', '')->timeout(86400);

        return $query;

    }

    /**
     * Get subject cursor for OCR processing.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function getSubjectErrorCursorForOcr(int $projectId, int $expeditionId = null): LazyCollection
    {
        $query = $this->model->where('project_id', $projectId);
        $query = null === $expeditionId ? $query : $query->where('expedition_id', $expeditionId);
        $query = $query->where('ocr', 'like', '%Error:%');

        return $query->cursor();

    }

    /**
     * Get subject cursor for OCR processing.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @param bool $error
     * @return int
     */
    public function getSubjectCountForOcr(int $projectId, int $expeditionId = null, bool $error = false): int
    {
        $query = $this->model->where('project_id', $projectId);
        $query = null === $expeditionId ? $query : $query->where('expedition_id', $expeditionId);
        $query = !$error ? $query->where('ocr', '') : $query->where('ocr', 'like', '%Error:%');

        return $query->count();

    }

    /**
     * Detach subjects from expedition.
     *
     * @param $subjectIds
     * @param $expeditionId
     */
    public function detachSubjects($subjectIds, $expeditionId)
    {
        $subjectIds->each(function ($subjectId) use ($expeditionId) {
            $subject = $this->model->find($subjectId);
            $subject->expedition_ids = collect($subject->expedition_ids)->diff($expeditionId)->toArray();
            $subject->save();
        });

        event('cache.flush', $expeditionId);
    }

    /**
     * Attach subjects to expedition.
     *
     * @param $subjectIds
     * @param $expeditionId
     */
    public function attachSubjects($subjectIds, $expeditionId)
    {
        $subjectIds->each(function ($subjectId) use ($expeditionId) {
            $subject = $this->model->find($subjectId);
            $subject->expedition_ids = collect($subject->expedition_ids)->push($expeditionId)->toArray();
            $subject->save();
        });

        event('cache.flush', $expeditionId);
    }

    /**
     * Delete unassigned by project id.
     *
     * @param int $projectId
     * @throws \Exception
     */
    public function deleteUnassignedByProject(int $projectId)
    {
        $this->model->where('project_id', $projectId)->where('expedition_ids', 'size', 0)->delete();
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
     * @return int Total number of rows
     * @throws \Exception
     */
    public function getGridTotalRowCount(array $vars = [])
    {
        $count = $this->model->whereNested(function ($query) use ($vars) {
            $this->buildQuery($query, $vars);
        })->options(['allowDiskUse' => true])->count();

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
    public function getGridRows(array $vars = [])
    {
        $query = $this->model->whereNested(function ($query) use ($vars) {
            $this->buildQuery($query, $vars);
        })->options(['allowDiskUse' => true])->take($vars['limit'])->skip($vars['offset']);

        foreach ($vars['orderBy'] as $field => $sort) {
            $query->orderBy($field, $sort);
        }

        $rows = $query->get();

        if (! is_array($rows)) {
            $rows = $rows->toArray();
        }

        $this->setRowCheckbox($rows);

        return $rows;
    }

    /**
     * Return query used to chunk rows for export.
     *
     * @param array $vars
     * @return \Illuminate\Support\LazyCollection
     */
    public function exportGridRows(array $vars): LazyCollection
    {
        $query = $this->model->whereNested(function ($query) use ($vars) {
            $this->buildQuery($query, $vars);
        })->options(['allowDiskUse' => true]);

        foreach ($vars['orderBy'] as $field => $sort) {
            $query->orderBy($field, $sort);
        }

        return $query->orderBy('_id', 'desc')->cursor();
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

        if (isset($vars['filters']['rules']) && is_array($vars['filters']['rules'])) {
            $rules = $vars['filters']['rules'];
            $query->where(function ($query) use ($rules) {
                $this->handleRules($query, $rules);
            });
        }

        $this->setExpeditionWhere($query, $vars);
    }

    /**
     * Set where for expedition ids depending on route.
     *
     * @param $query
     * @param $vars
     */
    protected function setExpeditionWhere(&$query, $vars)
    {
        // explore: Project Explore (show all)
        // show: Expedition Show page (show only assigned)
        // edit: Expedition edit (show all)
        // create: Expedition create (show not assigned)
        if ($vars['route'] === 'explore' || ($vars['route'] === 'edit' && $this->assignedRuleData === 'all')) {
            return;
        } elseif ($vars['route'] === 'show') {
            $this->setWhereIn($query, 'expedition_ids', [$vars['expeditionId']]);
        } elseif ($vars['route'] === 'create') {
            $this->setWhere($query, 'expedition_ids', 'size', 0);
        }
    }

    /**
     * Handle the passed filters.
     *
     * @param $query
     * @param $rules
     */
    protected function handleRules(&$query, $rules)
    {
        foreach ($rules as $rule) {
            if ($rule['field'] === 'assigned') {
                $this->assignedRule($query, $rule);
                continue;
            }

            if ($rule['field'] === 'expedition_ids') {
                $rule['data'] = (int) $rule['data'];
            }

            if ($rule['field'] === 'exported') {
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
     * 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn'
     * @param $rule
     * @param $query
     */
    protected function buildWhere(&$query, $rule)
    {
        switch ($rule['op']) {
            case 'bw':
                $this->setWhere($query, $rule['field'], 'regexp', '/^'.$rule['data'].'/i');
                break;
            case 'bn':
                $this->setWhere($query, $rule['field'], 'regexp', '/^(?!'.$rule['data'].').+/i');
                break;
            case 'ew':
                $this->setWhere($query, $rule['field'], 'regexp', '/^(?!'.'/'.$rule['data'].'$/i');
                break;
            case 'en':
                $this->setWhere($query, $rule['field'], 'regexp', '/.*(?<!'.$rule['data'].')$/i');
                break;
            case 'cn':
                $this->setWhere($query, $rule['field'], 'like', '%'.$rule['data'].'%');
                break;
            case 'nc':
                $this->setWhere($query, $rule['field'], 'not regexp', '/'.$rule['data'].'/i');
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
     *
     * @param $rule
     * @param $query
     *
     */
    protected function assignedRule(&$query, $rule)
    {
        $this->assignedRuleData = $rule['data'];

        if ($rule['data'] === 'all') {
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
        if ($rule['data'] === 'true') {
            $this->setWhereRaw($query, 'expedition_ids', ['$not' => ['$size' => 0]]);
        } else {
            $this->setWhere($query, 'expedition_ids', 'size', 0);
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
        if ($orderBy !== null) {
            $orderBys = explode(',', $orderBy);
            foreach ($orderBys as $order) {
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
        foreach ($rows as &$row) {
            $row['assigned'] = ! empty($row['expedition_ids']) ? 'Yes' : 'No';
        }
    }

    /**
     * Set group operator.
     *
     * @param $filters
     * @internal param $groupOp
     */
    protected function setGroupOp($filters)
    {
        $this->groupAnd = true;

        if (isset($filters['groupOp'])) {
            $this->groupAnd = ($filters['groupOp'] === 'AND');
        }
    }

    /**
     * Set groupOp process
     *
     * @param $bool
     */
    protected function setGroupOpProcessed($bool = false)
    {
        $this->groupOpProcessed = $bool;
    }

    /**
     * Set where/orWhere clause for query
     *
     * @param $query
     * @param $field
     * @param $operation
     * @param $data
     */
    protected function setWhere(&$query, $field, $operation, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->where($field, $operation, $data) :
            $query->orWhere($field, $operation, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereRaw/orWhereRaw for query
     *
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereRaw(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->whereRaw([$field => $data]) :
            $query->orWhereRaw([$field => $data]);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereIn/orWhereIn for query
     *
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereIn(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ? $query->whereIn($field, $data) : $query->orWhereIn($field, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereIn/orWhereIn for query
     *
     * @param $query
     * @param $field
     * @param $data
     */
    protected function setWhereNotIn(&$query, $field, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ? $query->whereNotIn($field, $data) : $query->orWhereNotIn($field, $data);

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereNull/orWhereNull for query
     *
     * @param $query
     * @param $field
     */
    protected function setWhereNull(&$query, $field)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ? $query->where($field, '') : $query->orWhereNull($field, '');

        $this->setGroupOpProcessed(true);
    }

    /**
     * Set whereNotNull/orWhereNotNull for query
     *
     * @param $query
     * @param $field
     */
    protected function setWhereNotNull(&$query, $field)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ? $query->whereNotNull($field, '!=', '') : $query->orWhereNotNull($field, '!=', '');

        $this->setGroupOpProcessed(true);
    }
}
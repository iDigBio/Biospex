<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * @inheritDoc
     */
    protected $connection = 'mongodb';

    /**
     * @inheritDoc
     */
    protected $primaryKey = '_id';

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $guarded = ['_id'];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panoptesTranscriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_subjectId');
    }

    /**
     * @return \Jenssegers\Mongodb\Relations\EmbedsOne
     */
    public function occurrence()
    {
        return $this->embedsOne(Occurrence::class, 'occurrence');
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
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeSubjectId($query, $id)
    {
        return $query->where('_id', $id);
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
     * Return count of project subjects not assigned to expeditions
     *
     * @param $projectId
     * @return mixed
     */
    public function loadUnassignedCount($projectId)
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
    public function loadSubjectIds($projectId, $take, $expeditionId)
    {
        $ids = $this->whereNested(function ($query) use ($projectId, $take, $expeditionId)
        {
            if ($expeditionId !== null)
            {
                $query->where('expedition_ids', '=', (int) $expeditionId);
            }
            else
            {
                $query->where('expedition_ids', 'size', 0);
            }

            $query->where('project_id', (int) $projectId);
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
    public function detachAllSubjects($ids, $expeditionId)
    {
        foreach ($ids as $id)
        {
            $array = [];
            $subject = $this->find($id);
            foreach ($subject->expedition_ids as $value)
            {
                if ((int) $expeditionId !== (int) $value)
                {
                    $array[] = $value;
                }
            }
            $subject->expedition_ids = $array;
            $subject->save();
        }

    }
    
    /**
     * Calculate the number of rows. It's used for paging the result.
     *
     * @param Collection $vars
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

     * @return int Total number of rows
     */
    public function getRowCount($vars)
    {
        $count = $this->whereNested(function ($query) use ($vars)
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
     */
    public function getAllRows($vars)
    {
        $orderByRaw = $this->setOrderBy($vars['sidx'], $vars['sord']);

        $vars['limit'] = ($vars['limit'] === 0) ? 1 : $vars['limit'];

        $query = $this->whereNested(function ($query) use ($vars)
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

        if ($vars['route'] !== 'web.grids.explore') {
            $this->setExpeditionWhere($query, $vars);
        }
    }

    protected function setExpeditionWhere(&$query, $vars)
    {
        // web.grids.explore: Project Explore (show all)
        // web.grids.show: Expedition Show page (show only assigned)
        // web.grids.edit: Expedition edit (show all)
        // web.grids.create: Expedition create (show not assigned)
        if ($vars['route'] === 'web.grids.edit')
        {
            if ($this->assignedRuleData === '' || $this->assignedRuleData === 'all')
                return;
        }
        elseif ($vars['route'] === 'web.grids.show')
        {
            $this->setWhereIn($query, 'expedition_ids', [$vars['expeditionId']]);
        }
        elseif ($vars['route'] === 'web.grids.create')
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
            if ($rule['field'] === 'expedition_ids')
            {
                $this->assignedRule($query, $rule);
                continue;
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
                list($field, $sort) = array_pad(explode(' ', $order, 2), 2, $sord);                
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
            $row['expedition_ids'] = ! empty($row['expedition_ids']) ? 'Yes' : 'No';
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
     * @param $op
     * @param $data
     */
    protected function setWhere(&$query, $field, $op, $data)
    {
        ($this->groupAnd || ! $this->groupOpProcessed) ?
            $query->where($field, $op, $data) : $query->orWhere($field, $op, $data);

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

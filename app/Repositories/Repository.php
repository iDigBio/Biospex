<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;

abstract class Repository
{

    /**
     * @var mixed
     */
    public $model;

    /**
     * @var array
     */
    protected $withRelations = [];

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * Create the model.
     *
     * @return mixed
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        
        return $this->model = $model;
    }

    /**
     * Reset the model.
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Return records.
     *
     * @param array $columns
     * @return mixed
     */
    public function get(array $columns = ['*'])
    {
        $result = $this->model->get($columns);
        
        $this->resetModel();
        
        return $result;
    }

    /**
     * Return first record.
     *
     * @param array $columns
     * @return mixed
     */
    public function first(array $columns = ['*'])
    {
        $result = $this->model->first($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Return all.
     *
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        $result = $this->model->all($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Find by id.
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, array $columns = ['*'])
    {
        $result = $this->model->find($id, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Return list.
     *
     * @param $value
     * @param $index
     * @return mixed
     */
    public function pluck($value, $index)
    {
        $result = $this->model->pluck($value, $index);

        $this->resetModel();

        return $result;
    }

    /**
     * Return count of records.
     *
     * @return mixed
     */
    public function count()
    {
        $result =  $this->model->count();

        $this->resetModel();

        return $result;
    }

    /**
     * Create record.
     *
     * @param $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();

        $this->resetModel();

        return $model;
    }

    /**
     * Update record.
     *
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($attributes)->save();

        $this->resetModel();

        return $model;
    }

    /**
     * Update or Create.
     * 
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values)
    {
        $model = $this->model->firstOrNew($attributes);
        $model->fill($values)->save();

        $this->resetModel();
        
        return $model;
    }

    /**
     * Destroy records.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $model = $this->find($id);

        $this->resetModel();

        return $model->delete();
    }

    /**
     * Save a model.
     *
     * @param $record
     * @return mixed
     */
    public function save($record)
    {
        $record->save();

        $this->resetModel();
        
        return $record;
    }

    /**
     * Set relationships.
     *
     * @param array $with
     * @return $this
     */
    public function with(array $with = [])
    {
        $this->withRelations = $with;
        $this->model = $this->model->with($with);

        return $this;
    }

    /**
     * Set where clause.
     *
     * @param array $where
     * @return $this
     */
    public function where(array $where = [])
    {
        $this->model = $this->model->whereNested(function ($query) use ($where)
        {
            $this->buildWhereClause($query, $where);
        });
        
        return $this;
    }

    /**
     * Set orWhere clause.
     *
     * @param array $where
     * @return $this
     */
    public function orWhere(array $where = [])
    {
        $this->model = $this->model->whereNested(function ($query) use ($where)
        {
            $this->buildWhereClause($query, $where, 'orWhere');
        });

        return $this;
    }

    /**
     * Find data using whereIn.
     *
     * @param array $where
     * @return $this
     */
    public function whereIn(array $where = [])
    {
        $this->model = $this->model->whereNested(function ($query) use ($where)
        {
            $this->buildWhereClause($query, $where, 'whereIn');
        });

        return $this;
    }

    /**
     * Find data using whereNotIn.
     *
     * @param array $where
     * @return $this
     */
    public function whereNotIn(array $where = [])
    {
        $this->model = $this->model->whereNested(function ($query) use ($where)
        {
            $this->buildWhereClause($query, $where, 'whereNotIn');
        });

        return $this;
    }

    /**
     * Find record based on relation.
     * 
     * @param $relation
     * @param array $where
     * @return $this
     */
    public function whereHas($relation, array $where = [])
    {
        $this->model = $this->model->whereHas($relation, function ($query) use ($where) {
            $this->buildWhereClause($query, $where);
        });
        
        return $this;
    }

    /**
     * Find record based on relation using Or.
     *
     * @param $relation
     * @param array $where
     * @return $this
     */
    public function orWhereHas($relation, array $where = [])
    {
        $this->model = $this->model->orWhereHas($relation, function ($query) use ($where) {
            $this->buildWhereClause($query, $where, 'orWhere');
        });

        return $this;
    }

    /**
     * Build raw where query.
     * 
     * @param array $where
     * @return $this
     */
    public function whereRaw(array $where = [])
    {
        $this->model = $this->model->whereRaw(function ($query) use ($where) {
            $this->buildWhereClause($query, $where, 'whereRaw');
        });
                
        return $this;
    }

    /**
     * Build where null query.
     * 
     * @param $column
     * @return $this
     */
    public function whereNull($column)
    {
        $this->model = $this->model->whereNull($column);
        
        return $this;
    }

    /**
     * Build where not null query.
     * 
     * @param $column
     * @return $this
     */
    public function whereNotNull($column)
    {
        $this->model = $this->model->whereNotNull($column);
        
        return $this;
    }

    /**
     * Find records using whereDate.
     * 
     * @param array $where
     * @return $this
     */
    public function whereDate(array $where = [])
    {
        $this->model = $this->model->whereDate(function ($query) use ($where) {
            $this->buildWhereClause($query, $where, 'whereDate');
        });

        return $this;
    }

    /**
     * Find records using orWhereDate.
     * 
     * @param array $where
     * @return $this
     */
    public function orWhereDate(array $where = [])
    {
        $this->model = $this->model->orWhereDate(function ($query) use ($where) {
            $this->buildWhereClause($query, $where, 'orWhereDate');
        });

        return $this;
    }

    /**
     * Find where model has relationships.
     * 
     * @param $relation
     * @param null $condition
     * @param null $value
     * @return $this
     */
    public function has($relation, $condition = null, $value = null)
    {
        if (null === $condition)
        {
            $this->model = $this->model->has($relation);
        }
        else{
            $this->model = $this->model->has($relation, $condition, $value);
        }
        
        return $this;
    }

    /**
     * Group by.
     * 
     * @param array $value
     * @return $this
     */
    public function groupBy($value)
    {
        $this->model = $this->model->groupBy($value);
        
        return $this;
    }

    /**
     * Set order by.
     *
     * @param array $order_by
     * @return $this
     */
    public function orderBy(array $order_by = [])
    {
        foreach ($order_by as $column => $sort)
        {
            $this->model = $this->model->orderBy($column, $sort);
        }

        return $this;
    }

    /**
     * Set limit and offset.
     *
     * @param $limit
     * @param int $offset
     * @return $this
     */
    public function limitOffset($limit, $offset = 0)
    {
        $this->model = $this->model->skip($offset)->limit($limit);

        return $this;
    }

    /**
     * Build where statements.
     *
     * @param $query
     * @param $where
     * @param $type
     */
    protected function buildWhereClause(&$query, $where, $type = 'where')
    {
        foreach ($where as $field => $value)
        {
            if (is_array($value))
            {
                list($field, $condition, $val) = $value;
                $query->{$type}($field, $condition, $val);
            }
            else
            {
                $query->{$type}($field, '=', $value);
            }
        }
    }

    /**
     * Get with relationships.
     *
     * @return mixed
     */
    public function getWith()
    {
        return $this->withRelations;
    }

    /**
     * Get only trashed.
     * 
     * @return $this
     */
    public function trashed()
    {
        $this->model = $this->model->onlyTrashed();

        return $this;
    }

    /**
     * Force delete model.
     * 
     * @param $id
     * @return mixed
     */
    public function forceDelete($id)
    {
        $model = $this->model->onlyTrashed()->find($id);
        
        $this->resetModel();

        return $model->forceDelete();
    }
}

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
        return $this->model = $this->app->make($this->model());
    }

    /**
     * Return records.
     *
     * @param array $columns
     * @return mixed
     */
    public function get(array $columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * Return first record.
     *
     * @param array $columns
     * @return mixed
     */
    public function first(array $columns = ['*'])
    {
        return $this->model->first($columns);
    }

    /**
     * Return all.
     *
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->all($columns);
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
        return $this->model->find($id, $columns);
    }

    /**
     * Return list.
     *
     * @param $value
     * @param $index
     * @return mixed
     */
    public function lists($value, $index)
    {
        return $this->model->lists($value, $index);
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

        return $model->save();
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

        return $model->fill($attributes)->save();
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
        return $record->save();
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
            $this->buildWhere($query, $where);
        });
        
        return $this;
    }

    /**
     * Find data by multiple values in one field.
     *
     * @param $field
     * @param array $values
     * @return $this
     */
    public function whereIn($field, array $values)
    {
        $this->model = $this->model->whereIn($field, $values);

        return $this;
    }

    /**
     * Find data by excluding multiple values in one field.
     *
     * @param $field
     * @param array $values
     * @return $this
     */
    public function whereNotIn($field, array $values)
    {
        $this->model = $this->model->whereNotIn($field, $values);

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
            $this->buildWhere($query, $where);
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
            $this->buildWhere($query, $where);
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
        $this->model = $this->model->whereRaw($where);
        
        return $this;
    }

    /**
     * Set order by.
     *
     * @param $column
     * @param string $sort
     * @return $this
     */
    public function orderBy($column, $sort = 'asc')
    {
        $this->model = $this->model->orderBy($column, $sort);

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
     */
    protected function buildWhere(&$query, $where)
    {
        foreach ($where as $field => $value)
        {
            if (is_array($value))
            {
                list($field, $condition, $val) = $value;
                $query->where($field, $condition, $val);
            }
            else
            {
                $query->where($field, '=', $value);
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
}

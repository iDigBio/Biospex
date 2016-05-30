<?php namespace App\Repositories\Contracts;

interface Repository
{
    public function get(array $columns = ['*']);

    public function first(array $columns = ['*']);
    
    public function all(array $columns = ['*']);
    
    public function find($id, array $columns = ['*']);
    
    public function lists($value, $index);

    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);

    public function save($record);
    
    public function with(array $with = []);
    
    public function where(array $where = []);

    public function whereIn($field, array $values);

    public function whereNotIn($field, array $values);

    public function whereHas($relation, array $where = []);

    public function orWhereHas($relation, array $where = []);

    public function whereRaw(array $where = []);

    public function orderBy($column, $sort = 'asc');

    public function limitOffset($limit, $offset = 0);
}

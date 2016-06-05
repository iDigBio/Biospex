<?php namespace App\Repositories\Contracts;

interface Repository
{
    public function get(array $columns = ['*']);

    public function first(array $columns = ['*']);
    
    public function all(array $columns = ['*']);
    
    public function find($id, array $columns = ['*']);
    
    public function lists($value, $index);

    public function count();

    public function create(array $attributes);

    public function update(array $attributes, $id);

    public function delete($id);

    public function save($record);
    
    public function with(array $with = []);
    
    public function where(array $where = []);
    
    public function orWhere(array $where = []);

    public function whereIn(array $where = []);

    public function whereNotIn(array $where = []);

    public function whereHas($relation, array $where = []);

    public function orWhereHas($relation, array $where = []);

    public function whereRaw(array $where = []);

    public function groupBy($value);

    public function orderBy(array $order_by = []);

    public function limitOffset($limit, $offset = 0);
}

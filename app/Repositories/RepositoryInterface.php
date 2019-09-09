<?php

namespace App\Repositories;


interface RepositoryInterface
{
    public function all(array $columns = ['*']);

    public function find($resourceId, array $columns = ['*']);

    public function findBy($field, $value, array $columns = ['*']);

    public function findWith($resourceId, array $with = []);

    public function whereIn($field, array $values, array $columns = ['*']);

    public function create(array $data);

    public function firstOrCreate(array $attributes, array $data = []);

    public function update(array $data, $resourceId);

    public function updateOrCreate(array $attributes, array $values);

    public function delete($model);

    public function truncate();
}
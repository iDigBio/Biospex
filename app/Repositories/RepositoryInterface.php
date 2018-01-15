<?php

namespace App\Repositories;


interface RepositoryInterface
{
    public function all(array $columns = ['*']);

    public function find($resourceId, array $columns = ['*']);

    public function findBy($field, $value, array $columns = ['*']);

    public function findWith($resourceId, array $with = []);

    public function findOnlyTrashed($resourceId, array $with = []);

    public function getWhereIn($field, array $values, array $columns = ['*']);

    public function getOnlyTrashed(array $columns = ['*']);

    public function create(array $data);

    public function firstOrCreate(array $attributes);

    public function update(array $data, $resourceId);

    public function updateOrCreate(array $attributes, array $values);

    public function delete($model);

    public function destroy($model);

    public function restore($model);

    public function truncate();
}
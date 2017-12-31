<?php

namespace App\Interfaces;

interface Eloquent
{

    public function all(array $columns = ['*']);

    public function find($id, array $columns = ['*']);

    public function findBy($field, $value, array $columns = ['*']);

    public function findWith($id, array $with = []);

    public function findOnlyTrashed($id, array $with = []);

    public function getWhereIn($field, array $values, array $columns = ['*']);

    public function getOnlyTrashed(array $columns = ['*']);

    public function create(array $data);

    public function firstOrCreate(array $attributes);

    public function update(array $data, $id);

    public function updateOrCreate(array $attributes, array $values);

    public function delete($model);

    public function destroy($model);

    public function restore($model);

    public function truncate();
}
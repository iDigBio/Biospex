<?php namespace App\Repositories\Contracts;

interface Repository
{
    public function all();
    
    public function allWith($with);

    public function find($id);

    public function create($data);

    public function update($data);

    public function destroy($id);

    public function findWith($id, $with);

    public function save($record);
}

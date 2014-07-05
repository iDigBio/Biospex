<?php namespace Biospex\Repo;
/**
 * Repository.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * The Abstract Repository provides default implementations of the methods expected
 * in models. The calls are based on Eloquent, so alternative models
 * would need to implement these calls and handle them appropriately.
 */

abstract class Repository {

    /**
     * @var
     */
    protected $model;

    /**
     * Return all
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->model->all($columns);
    }


    /**
     * Find by id. Enable eager loading using with.
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function find($id, array $columns = array('*'))
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update(array $input)
    {
        $model = $this->find($input['id']);
        return $model->update($input);
    }


    /**
     * Destroy records
     * 
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, array $with = array())
    {
        $query = $this->make($with);

        return $query->find($id);
    }

    /**
     * Make a new instance of the entity to query on
     *
     * @param array $with
     */
    public function make(array $with = array())
    {
        return $this->model->with($with);
    }

    public function save($record)
    {
        return $record->save();
    }

}
<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Project;

abstract class AbstractProjectDecorator implements Project
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Return all
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->project->all($columns);
    }

    /**
     * Find by id
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->project->find($id, $columns);
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data = array())
    {
        return $this->project->create($data);
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data = array())
    {
        return $this->project->update($data);
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->project->destroy($id);
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with = array())
    {
        return $this->project->findWith($id, $with);
    }

    /**
     * Find by uuid.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->project->findByUuid($uuid);
    }
}

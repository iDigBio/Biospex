<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Group;

abstract class AbstractGroupDecorator implements Group
{
    protected $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Return all
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->group->all($columns);
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
        return $this->group->find($id, $columns);
    }

    /**
     * Find all groups
     * @return mixed
     */
    public function findAllGroups()
    {
        return $this->group->findAllGroups();
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data = array())
    {
        return $this->group->create($data);
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data = array())
    {
        return $this->group->update($data);
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->group->destroy($id);
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
        return $this->group->findWith($id, $with);
    }
}

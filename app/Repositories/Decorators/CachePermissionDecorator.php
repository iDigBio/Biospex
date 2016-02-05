<?php

namespace app\Repositories\Decorators;

use Biospex\Repositories\Contracts\Permission;

class CachePermissionDecorator extends CacheDecorator implements Permission
{

    /**
     * Return all records of resource.
     *
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);

        return parent::all();
    }

    /**
     * Find by id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $this->setKey(__METHOD__, $id);

        return parent::find($id);
    }

    /**
     * Find using id and relationships.
     *
     * @param $id
     * @param $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $this->setKey(__METHOD__, $id . implode('.', $with));

        return parent::findWith($id, $with);
    }

}
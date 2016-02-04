<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Expedition;

class CacheExpeditionDecorator extends CacheDecorator implements Expedition
{

    /**
     * Return all records in resource.
     *
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);

        return parent::all();
    }

    /**
     * Find record by id.
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
     * Find with eager loading.
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $this->setKey(__METHOD__, $id . implode('.', $with));

        return parent::findWith($id, $with);
    }

    /**
     * Find by uuid using cache or query.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        if ( ! $this->cached) {
            return $this->repository->findByUuid($uuid);
        }

        $this->setKey(__METHOD__, $uuid);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($uuid) {
            return $this->repository->findByUuid($uuid);
        });
    }
}

<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Expedition;

class CacheExpeditionDecorator extends CacheDecorator implements Expedition
{
    
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

    public function getAllExpeditions($id)
    {
        
        if ( ! $this->cached) {
            return $this->repository->getAllExpeditions($id);
        }

        $this->setKey(__METHOD__, $id);
        
        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($id) {
            return $this->repository->getAllExpeditions($id);
        });
    }
}

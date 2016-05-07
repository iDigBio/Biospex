<?php

namespace App\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as Cache;

class CacheDecorator
{

    /**
     * @var
     */
    protected $repository;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var
     */
    protected $tag;

    /**
     * @var
     */
    protected $key;

    /**
     * Use cached data.
     * @var bool
     */
    protected $cached = true;

    /**
     * CacheDecorator constructor.
     * @param Cache $cache
     * @param $repository
     * @param $tag
     */
    public function __construct(Cache $cache, $repository, $tag)
    {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->tag = $tag;
    }

    /**
     * Set cache bypass.
     * @param bool|true $bool
     */
    public function cached($bool = true)
    {
        $this->cached = $bool;
    }

    /**
     * Build cache key.
     * @param $method
     * @param null $serial
     * @return string
     */
    protected function setKey($method, $serial = null)
    {
        $this->key = md5(get_class($this->repository) . '::' . $method . serialize($serial));
    }

    /**
     * Retrieve all records for resource.
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);
        
        if ( ! $this->cached) {
            return $this->repository->all();
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () {
            return $this->repository->all();
        });
    }

    /**
     * Returns all with relationships.
     * @param $with
     * @return mixed
     */
    public function allWith($with)
    {
        $this->setKey(__METHOD__, $with);
        
        if ( ! $this->cached) {
            return $this->repository->allWith($with);
        }
        
        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($with) {
            return $this->repository->allWith($with);
        });
    }

    /**
     * Find resource using id.
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $this->setKey(__METHOD__, $id);
        
        if ( ! $this->cached) {
            return $this->repository->find($id);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * Find with eager loading.
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
        $this->setKey(__METHOD__, $id . implode('.', $with));
        
        if ( ! $this->cached) {
            return $this->repository->findWith($id, $with);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($id, $with) {
            return $this->repository->findWith($id, $with);
        });
    }

    /**
     * Save
     *
     * @param $record
     * @return mixed
     */
    public function save($record)
    {
        return $this->repository->save($record);
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update record.
     *
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        return $this->repository->update($data);
    }


    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }
}
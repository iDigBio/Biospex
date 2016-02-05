<?php

namespace Biospex\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as Cache;

class CacheDecorator
{
    /**
     * Repository.
     *
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
     * Cache key.
     * @var
     */
    protected $key;

    /**
     * Use cached data.
     *
     * @var bool
     */
    protected $cached = true;

    public function __construct(Cache $cache, $repository, $tag)
    {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->tag = $tag;
    }

    /**
     * Set cache bypass.
     *
     * @param bool|true $bool
     */
    public function cached($bool = true)
    {
        $this->cached = $bool;
    }

    /**
     * Build cache key.
     *
     * @param $method
     * @param null $serial
     * @return string
     */
    protected function setKey($method, $serial = null)
    {
        $this->key = md5($method . serialize($serial));
    }

    /**
     * Retrieve all records for resource.
     *
     * @return mixed
     */
    public function all()
    {
        if ( ! $this->cached) {
            return $this->repository->all();
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () {
            return $this->repository->all();
        });
    }

    /**
     * Find resource using id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        if ( ! $this->cached) {
            return $this->repository->find($id);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with)
    {
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
        $group = $this->repository->save($record);
        $this->cache->tags($this->tag)->flush();

        return $group;
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data)
    {
        $record = $this->repository->create($data);
        $this->cache->tags($this->tag)->flush();

        return $record;
    }

    /**
     * Update record.
     *
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        $record = $this->repository->update($data);
        $this->cache->tags($this->tag)->flush();

        return $record;
    }


    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $record = $this->repository->destroy($id);
        $this->cache->tags($this->tag)->flush();

        return $record;
    }
}
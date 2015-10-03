<?php namespace App\Repositories\Decorators;

use App\Services\Cache\Cache;
use App\Repositories\Contracts\Expedition;

class CacheExpeditionDecorator extends AbstractExpeditionDecorator
{
    /**
     * @var Cache
     */
    protected $cache;

    protected $pass = false;

    /**
     * Constructor
     *
     * @param ExpeditionInterface $expedition
     * @param CacheInterface $cache
     */
    public function __construct(Expedition $expedition, Cache $cache)
    {
        parent::__construct($expedition);
        $this->cache = $cache;
    }

    /**
     * All
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $key = md5('expeditions.all');

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        if (! $this->pass) {
            $expeditions = $this->expedition->all();
        }

        $this->cache->put($key, $expeditions);

        return $expeditions;
    }

    /**
     * Find
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $key = md5('expedition.' . $id);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $expedition = $this->expedition->find($id, $columns);

        if (! $this->pass) {
            $this->cache->put($key, $expedition);
        }

        return $expedition;
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data = [])
    {
        $expedition = $this->expedition->create($data);
        $this->cache->flush();

        return $expedition;
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data = [])
    {
        $expedition = $this->expedition->update($data);
        $this->cache->flush();

        return $expedition;
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $expedition = $this->expedition->destroy($id);
        $this->cache->flush();

        return $expedition;
    }

    /**
     * Find with eager loading
     *
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findWith($id, $with = [])
    {
        $key = md5('expedition.' . $id . implode(".", $with));

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $expedition = $this->expedition->findWith($id, $with);

        if (! $this->pass) {
            $this->cache->put($key, $expedition);
        }

        return $expedition;
    }

    /**
     * Save
     *
     * @param $record
     * @return mixed
     */
    public function save($record)
    {
        $expedition = $this->expedition->save($record);
        $this->cache->flush();

        return $expedition;
    }

    /**
     * Find by uuid using cache or query.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        $key = md5('expedition.' . $uuid);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $project = $this->project->findByUuid($uuid);

        if (! $this->pass) {
            $this->cache->put($key, $project);
        }

        return $project;
    }

    /**
     * Set cache pass
     *
     * @param bool $value
     */
    public function setPass($value = false)
    {
        $this->pass = $value;
    }
}

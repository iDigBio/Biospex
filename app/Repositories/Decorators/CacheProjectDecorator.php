<?php namespace App\Repositories\Decorators;

use App\Services\Cache\Cache;
use App\Repositories\Contracts\Project;

class CacheProjectDecorator extends AbstractProjectDecorator
{
    protected $cache;
    protected $pass;

    /**
     * Constructor
     *
     * @param Project $project
     * @param Cache $cache
     */
    public function __construct(Project $project, Cache $cache)
    {
        parent::__construct($project);
        $this->cache = $cache;
    }

    /**
     * All
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $key = md5('projects.all');

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $projects = $this->project->all();

        if (! $this->pass) {
            $this->cache->put($key, $projects);
        }

        return $projects;
    }

    /**
     * Find
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        $key = md5('project.' . $id);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $project = $this->project->find($id, $columns);

        if (! $this->pass) {
            $this->cache->put($key, $project);
        }

        return $project;
    }

    /**
     * Create record
     *
     * @param array $data
     * @return mixed
     */
    public function create($data = array())
    {
        $project = $this->project->create($data);
        $this->cache->flush();

        return $project;
    }

    /**
     * Update record
     *
     * @param array $input
     * @return mixed
     */
    public function update($data = array())
    {
        $project = $this->project->update($data);
        $this->cache->flush();

        return $project;
    }

    /**
     * Destroy records
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $project = $this->project->destroy($id);
        $this->cache->flush();

        return $project;
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
        $key = md5('project.' . $id . implode(".", $with));

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $project = $this->project->findWith($id, $with);

        if (! $this->pass) {
            $this->cache->put($key, $project);
        }

        return $project;
    }

    /**
     * Save
     *
     * @param $record
     * @return mixed
     */
    public function save($record)
    {
        $project = $this->project->save($record);
        $this->cache->flush();

        return $project;
    }

    /**
     * By slug
     *
     * @param $slug
     * @return mixed
     */
    public function bySlug($slug)
    {
        $key = md5('project.' . $slug);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $project = $this->project->bySlug($slug);

        if (! $this->pass) {
            $this->cache->put($key, $project);
        }

        return $project;
    }

    /**
     * Find by uuid using cache or query.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        $key = md5('project.' . $uuid);

        if ($this->cache->has($key) && ! $this->pass) {
            return $this->cache->get($key);
        }

        $project = $this->project->findByUuid($uuid);

        if (! $this->pass) {
            $this->cache->put($key, $project);
        }

        return $project;
    }

    public function setPass($value = false)
    {
        $this->pass = $value;
    }
}

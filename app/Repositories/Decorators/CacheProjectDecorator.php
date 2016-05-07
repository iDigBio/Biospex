<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Project;

class CacheProjectDecorator extends CacheDecorator implements Project
{

    /**
     * By slug
     *
     * @param $slug
     * @return mixed
     */
    public function bySlug($slug)
    {
        if (!$this->cached)
        {
            return $this->repository->bySlug($slug);
        }

        $this->setKey(__METHOD__, $slug);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($slug)
        {
            return $this->repository->bySlug($slug);
        });
    }

    /**
     * Find by uuid using cache or query.
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        if (!$this->cached)
        {
            return $this->repository->findByUuid($uuid);
        }

        $this->setKey(__METHOD__, $uuid);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($uuid)
        {
            return $this->repository->findByUuid($uuid);
        });
    }

    /**
     * Get subjects assigned count
     *
     * @param $project
     * @return mixed
     */
    public function getSubjectsAssignedCount($project)
    {
        if (!$this->cached)
        {
            return $this->repository->getSubjectsAssignedCount($project);
        }

        $this->setKey(__METHOD__, $project);

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($project)
        {
            return $this->repository->getSubjectsAssignedCount($project);
        });
    }
}

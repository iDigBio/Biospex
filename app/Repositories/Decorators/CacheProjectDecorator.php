<?php namespace App\Repositories\Decorators;

use App\Repositories\Contracts\Project;

class CacheProjectDecorator extends CacheDecorator implements Project
{

    /**
     * All
     *
     * @param array $columns
     * @return mixed
     */
    public function all()
    {
        $this->setKey(__METHOD__);

        return parent::all();
    }

    /**
     * Find
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id)
    {
        $this->setKey(__METHOD__, $id);

        return parent::find($id);
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
        $this->setKey(__METHOD__, $id . implode('.', $with));

        return parent::findWith($id, $with);
    }


    /**
     * By slug
     *
     * @param $slug
     * @return mixed
     */
    public function bySlug($slug)
    {
        $this->setKey(__METHOD__, $slug);

        if ( ! $this->cached) {
            return $this->repository->bySlug($slug);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($slug) {
            return $this->repository->findWith($slug);
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
        $this->setKey(__METHOD__, $uuid);

        if ( ! $this->cached) {
            return $this->repository->findByUuid($uuid);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($uuid) {
            return $this->repository->findByUuid($uuid);
        });
    }

    public function getSubjectsAssignedCount($project)
    {
        $this->setKey(__METHOD__, $project);

        if (! $this->cached) {
            return $this->repository->getSubjectsAssignedCount($project);
        }

        return $this->cache->tags($this->tag)->rememberForever($this->key, function () use ($project) {
            return $this->repository->getSubjectsAssignedCount($project);
        });
    }
}

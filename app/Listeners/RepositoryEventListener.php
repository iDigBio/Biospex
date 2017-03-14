<?php

namespace App\Listeners;

use App\Repositories\Contracts\RepositoryContract;
use Illuminate\Contracts\Events\Dispatcher;

class RepositoryEventListener
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('*.entity.creating', __CLASS__.'@entityCreating');
        $dispatcher->listen('*.entity.created', __CLASS__.'@entityCreated');
        $dispatcher->listen('*.entity.updating', __CLASS__.'@entityUpdating');
        $dispatcher->listen('*.entity.updated', __CLASS__.'@entityUpdated');
        $dispatcher->listen('*.entity.deleting', __CLASS__.'@entityDeleting');
        $dispatcher->listen('*.entity.deleted', __CLASS__.'@entityDeleted');
    }

    /**
     * Listen to entities being created.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityCreating(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities created.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityCreated(RepositoryContract $repository, $entity)
    {

        $clearOn = $repository->getContainer('config')->get('repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('create', $clearOn)) {
            $repository->forgetCache();
        }
    }

    /**
     * Listen to entities being updated.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityUpdating(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities updated.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityUpdated(RepositoryContract $repository, $entity)
    {
        $clearOn = $repository->getContainer('config')->get('repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('update', $clearOn)) {
            $repository->forgetCache();
        }
    }

    /**
     * Listen to entities being deleted.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityDeleting(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities deleted.
     *
     * @param RepositoryContract $repository
     * @param $entity
     */
    public function entityDeleted(RepositoryContract $repository, $entity)
    {
        $clearOn = $repository->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('delete', $clearOn)) {
            $repository->forgetCache();
        }
    }
}

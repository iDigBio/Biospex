<?php

namespace App\Listeners;

use App\Jobs\BuildExpeditionOcrFile;
use App\Jobs\ExportQueueJob;
use App\Repositories\Contracts\RepositoryContract;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Events\Dispatcher;

class ExpeditionEventListener
{
    use DispatchesJobs;

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('biospex.repository.expedition.entity.creating', __CLASS__.'@entityCreating');
        $dispatcher->listen('biospex.repository.expedition.entity.created', __CLASS__.'@entityCreated');
        $dispatcher->listen('biospex.repository.expedition.entity.updating', __CLASS__.'@entityUpdating');
        $dispatcher->listen('biospex.repository.expedition.entity.updated', __CLASS__.'@entityUpdated');
        $dispatcher->listen('biospex.repository.expedition.entity.deleting', __CLASS__.'@entityDeleting');
        $dispatcher->listen('biospex.repository.expedition.entity.deleted', __CLASS__.'@entityDeleted');
    }

    /**
     * Entity Creating.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityCreating(RepositoryContract $repositoryContract, $entity)
    {

    }

    /**
     * Entity Updated.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityCreated(RepositoryContract $repositoryContract, $entity)
    {
        $this->dispatch((new BuildExpeditionOcrFile($entity))->onQueue(config('config.beanstalkd.default')));
    }

    /**
     * Entity Updating.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityUpdating(RepositoryContract $repositoryContract, $entity)
    {

    }

    /**
     * Entity Updated.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityUpdated(RepositoryContract $repositoryContract, $entity)
    {
        dd($entity);
        $this->dispatch((new BuildExpeditionOcrFile($entity))->onQueue(config('config.beanstalkd.default')));
    }

    /**
     * Entity Deleting.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityDeleting(RepositoryContract $repositoryContract, $entity)
    {

    }

    /**
     * Entity Deleted.
     *
     * @param RepositoryContract $repositoryContract
     * @param $entity
     */
    public function entityDeleted(RepositoryContract $repositoryContract, $entity)
    {

    }
}

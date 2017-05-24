<?php

namespace App\Listeners;

use App\Jobs\StagedQueueJob;
use App\Repositories\Contracts\RepositoryContract;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Events\Dispatcher;
use Config;

class StagedQueueEventListener
{
    use DispatchesJobs;

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('biospex.repository.stagedQueue.entity.creating', __CLASS__.'@entityCreating');
        $dispatcher->listen('biospex.repository.stagedQueue.entity.created', __CLASS__.'@entityCreated');
        $dispatcher->listen('biospex.repository.stagedQueue.entity.updating', __CLASS__.'@entityUpdating');
        $dispatcher->listen('biospex.repository.stagedQueue.entity.updated', __CLASS__.'@entityUpdated');
        $dispatcher->listen('biospex.repository.stagedQueue.entity.deleting', __CLASS__.'@entityDeleting');
        $dispatcher->listen('biospex.repository.stagedQueue.entity.deleted', __CLASS__.'@entityDeleted');
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
     *
     * @see NfnPanoptesExport::stagedQueue() Fired when new exports added.
     * @see StagedQueueJob::handle() Call job if a record exists and not queued.
     * @see StagedQueueRepository::getFirst() Get first record with no error.
     */
    public function entityCreated(RepositoryContract $repositoryContract, $entity)
    {
        $record = $repositoryContract->setCacheLifetime(0)->getFirst();
        if ($record !== null && ! $record->queued)
        {
            $repositoryContract->update($record->id, ['queued' => 1]);
        }
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
     * @see StagedQueueRepository::getFirst() Get first record with no error.
     */
    public function entityUpdated(RepositoryContract $repositoryContract, $entity)
    {
        if ($entity->queued && ! $entity->error)
        {
            $this->dispatchJob($entity);

            return;
        }

        $record = $repositoryContract->setCacheLifetime(0)->getFirst();
        if ($record !== null && ! $record->queued)
        {
            $repositoryContract->update($record->id, ['queued' => 1]);
        }

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
        $record = $repositoryContract->setCacheLifetime(0)->getFirst();
        if ($record !== null && ! $record->queued)
        {
            $repositoryContract->update($record->id, ['queued' => 1]);
        }
    }

    /**
     * Dispatch job.
     *
     * @param $record
     */
    public function dispatchJob($record)
    {
        $this->dispatch((new StagedQueueJob($record))->onQueue(Config::get('config.beanstalkd.staged')));
    }
}

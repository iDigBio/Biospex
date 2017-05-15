<?php

namespace App\Repositories\Contracts;

interface ExportQueueContract extends RepositoryContract, CacheableContract
{

    /**
     * Check for queued job.
     *
     * @return int
     */
    public function checkForQueuedJob();
}
<?php

namespace App\Repositories\Contracts;

interface ExportJobQueueContract extends RepositoryContract, CacheableContract
{

    /**
     * Check for queued job.
     *
     * @return int
     */
    public function checkForQueuedJob();
}
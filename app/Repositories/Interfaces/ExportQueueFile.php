<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface ExportQueueFile extends RepositoryInterface
{
    /**
     * Get files from export_queue_files by queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesByQueueId(string $queueId): Collection;

    /**
     * Get files for queue where no error listed.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithoutErrorByQueueId(string $queueId): Collection;

    /**
     * Get queue files with errors using queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithErrorsByQueueId(string $queueId): Collection;
}

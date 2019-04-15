<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface OcrQueue extends RepositoryInterface
{
    /**
     * @return mixed
     */
    public function getOcrQueuesForPollCommand();

    /**
     * @return mixed
     */
    public function getOcrQueueForOcrProcessCommand();
}

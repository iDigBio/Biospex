<?php

namespace App\Interfaces;

interface OcrQueue extends Eloquent
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

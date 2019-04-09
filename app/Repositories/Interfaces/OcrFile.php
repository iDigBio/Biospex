<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface OcrFile extends RepositoryInterface
{
    /**
     * Get all files for processing ocr by queue id.
     *
     * @param $queueId
     * @return mixed
     */
    public function getAllOcrQueueFiles($queueId);

}
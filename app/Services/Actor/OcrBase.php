<?php

namespace App\Services\Actor;

use Storage;

class OcrBase
{
    /**
     * @var
     */
    protected $folderPath;

    /**
     * Create directory for queue.
     *
     * @param $queueId
     */
    protected function setDir($queueId)
    {
        $this->folderPath = 'ocr/'.md5($queueId);

        if (! Storage::exists($this->folderPath)) {
            Storage::makeDirectory($this->folderPath);
        }
    }

    /**
     * Delete directory for queue.
     */
    protected function deleteDir()
    {
        Storage::deleteDirectory($this->folderPath);
    }
}
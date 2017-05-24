<?php

namespace App\Services\Actor;

class ActorServiceBase
{
    public $workingDirectory;

    /**
     * @return mixed
     */
    public function getScratchDirectory()
    {
        return config('config.scratch_dir');
    }

    /**
     * Set working directory.
     *
     * @param $directory
     * @return string
     */
    public function setWorkingDirectory($directory)
    {
        return $this->workingDirectory = $this->getScratchDirectory() . '/' . $directory;
    }
}
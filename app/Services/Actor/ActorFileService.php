<?php

namespace App\Services\Actor;

use App\Services\File\FileService;

class ActorFileService extends ActorServiceBase
{

    /**
     * @var FileService
     */
    public $fileService;

    /**
     * ActorFileService constructor.
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        parent::__construct();

        $this->fileService = $fileService;
    }

    /**
     * Create and set working directory.
     *
     * @param $folder
     * @return string
     */
    public function createAndSetWorkingDirectories($folder)
    {
        $directory = $this->scratchDirectory . '/' . $folder;
        $this->fileService->makeDirectory($this->scratchDirectory . '/' . $folder);

        return $directory;
    }
}
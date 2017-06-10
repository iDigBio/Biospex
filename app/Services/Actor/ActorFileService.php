<?php

namespace App\Services\Actor;

use App\Services\File\FileService;

class ActorFileService extends ActorServiceBase
{
    /**
     * @var
     */
    protected $config;

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
        $this->fileService = $fileService;
    }

    public function setActorServiceConfig(ActorServiceConfig $config)
    {
        $this->config = $config;
    }

}
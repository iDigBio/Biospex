<?php

namespace App\Services\Actor;

use App\Exceptions\CreateDirectoryException;
use Illuminate\Config\Repository as Config;
use App\Services\Report\Report;
use App\Exceptions\Handler;

class ActorService
{
    /**
     * @var Config
     */
    public  $config;

    /**
     * @var Report
     */
    public $report;

    /**
     * @var ActorApiService
     */
    public $actorApiService;

    /**
     * @var ActorImageService
     */
    public $actorImageService;

    /**
     * @var ActorRepositoryService
     */
    public $actorRepoService;

    /**
     * @var mixed
     */
    public $scratchDir;

    /**
     * @var
     */
    public $workingDir;

    /**
     * @var Handler
     */
    public $handler;

    /**
     * Actor constructor.
     *
     * @param Config $config
     * @param Report $report
     * @param ActorApiService $actorApiService
     * @param ActorImageService $actorImageService
     * @param ActorRepositoryService $actorRepoService
     * @param Handler $handler
     */
    public function __construct(
        Config $config,
        Report $report,
        ActorApiService $actorApiService,
        ActorImageService $actorImageService,
        ActorRepositoryService $actorRepoService,
        Handler $handler
    )
    {

        $this->config = $config;
        $this->report = $report;
        $this->actorApiService = $actorApiService;
        $this->fileService = $actorImageService->fileService;
        $this->actorImageService = $actorImageService;
        $this->actorRepoService = $actorRepoService;
        $this->handler = $handler;

        $this->scratchDir = $config->get('config.scratch_dir');
    }

    /**
     * Set working directory for actors.
     *
     * @param $folder
     * @throws  CreateDirectoryException
     */
    public function setWorkingDirectory($folder)
    {
        $this->workingDir = $this->scratchDir . '/' . $folder;
        $this->fileService->makeDirectory($this->workingDir);
    }
}
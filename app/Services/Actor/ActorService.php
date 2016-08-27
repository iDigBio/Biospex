<?php

namespace App\Services\Actor;

use Illuminate\Config\Repository as Config;
use App\Services\Report\Report;

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
     * Actor constructor.
     *
     * @param Config $config
     * @param Report $report
     * @param ActorApiService $actorApiService
     * @param ActorImageService $actorImageService
     * @param ActorRepositoryService $actorRepoService
     */
    public function __construct(
        Config $config,
        Report $report,
        ActorApiService $actorApiService,
        ActorImageService $actorImageService,
        ActorRepositoryService $actorRepoService
    )
    {

        $this->config = $config;
        $this->report = $report;
        $this->actorApiService = $actorApiService;
        $this->actorFileService = $actorImageService->actorFileService;
        $this->actorImageService = $actorImageService;
        $this->actorRepoService = $actorRepoService;

        $this->scratchDir = $config->get('config.scratch_dir');
    }

    /**
     * Set working directory for actors.
     *
     * @param $folder
     * @throws  \RuntimeException
     */
    public function setWorkingDirectory($folder)
    {
        $this->workingDir = $this->scratchDir . '/' . $folder;
        $this->actorFileService->makeDirectory($this->workingDir);
    }

    /**
     * Report complete process.
     *
     * @param array $vars (title, message, groupId, attachmentName)
     * @param array $missingImages
     */
    public function processComplete($vars, array $missingImages = [])
    {
        $this->report->processComplete($vars, $missingImages);
    }
}
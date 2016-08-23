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
    public $apiService;

    /**
     * @var ActorFileService
     */
    public $fileService;

    /**
     * @var ActorImageService
     */
    public $imageService;

    /**
     * @var ActorRepositoryService
     */
    public $repositoryService;

    /**
     * @var mixed
     */
    public $scratchDir;

    /**
     * @var
     */
    public $workingDir;

    /**
     * @var
     */
    public $workingDirOrig;

    /**
     * @var
     */
    public $workingDirConvert;

    /**
     * Actor constructor.
     *
     * @param Config $config
     * @param Report $report
     * @param ActorApiService $apiService
     * @param ActorFileService $fileService
     * @param ActorImageService $imageService
     * @param ActorRepositoryService $repositoryService
     */
    public function __construct(
        Config $config,
        Report $report,
        ActorApiService $apiService,
        ActorFileService $fileService,
        ActorImageService $imageService,
        ActorRepositoryService $repositoryService
    )
    {

        $this->config = $config;
        $this->report = $report;
        $this->apiService = $apiService;
        $this->fileService = $fileService;
        $this->imageService = $imageService;
        $this->repositoryService = $repositoryService;

        $this->scratchDir = $config->get('config.scratch_dir');
    }

    /**
     * Set working directories for actors.
     *
     * @param $folder
     * @throws  \RuntimeException
     */
    public function setWorkingDirectories($folder)
    {
        $this->workingDir = $this->scratchDir . '/' . $folder;
        $this->fileService->makeDirectory($this->workingDir);
        $this->workingDirOrig = $this->workingDir . '/orig';
        $this->fileService->makeDirectory($this->workingDirOrig);
        $this->workingDirConvert = $this->workingDir . '/' . $folder;
        $this->fileService->makeDirectory($this->workingDirConvert);
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
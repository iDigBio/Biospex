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
    }

    /**
     * Report complete process.
     *
     * @param $record
     * @param array $missingImages
     */
    public function processComplete($record, array $missingImages = [])
    {
        $group_id = $record->project->group_id;
        $title = $record->title;
        $missingImg = $missingImages;
        $name = $record->id . '-missing_images';

        $this->report->processComplete($group_id, $title, $missingImg, $name);

    }
}
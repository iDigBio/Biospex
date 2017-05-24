<?php

namespace App\Console\Commands;

use App\Jobs\StagedQueueJob;
use App\Models\Actor;
use App\Models\Expedition;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\StagedQueueContract;
use App\Repositories\Contracts\SubjectContract;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\Actor\NfnPanoptes\NfnPanoptesExport;
use App\Services\File\FileService;
use App\Services\Requests\HttpRequest;
use Event;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var HttpRequest
     */
    private $httpRequest;
    private $actor;
    private $subjects;
    /**
     * @var ActorImageService
     */
    private $actorImageService;
    /**
     * @var FileService
     */
    private $fileService;
    /**
     * @var ActorRepositoryService
     */
    private $actorRepositoryService;
    /**
     * @var NfnPanoptesExport
     */
    private $nfnPanoptesExport;

    /**
     * TestAppCommand constructor.
     * @param NfnPanoptesExport $nfnPanoptesExport
     * @param ActorImageService $actorImageService
     * @param ActorRepositoryService $actorRepositoryService
     * @param FileService $fileService
     */
    public function __construct(
        NfnPanoptesExport $nfnPanoptesExport,
        ActorImageService $actorImageService,
        ActorRepositoryService $actorRepositoryService,
        FileService $fileService
    )
    {
        parent::__construct();

        $this->actorImageService = $actorImageService;
        $this->actorRepositoryService = $actorRepositoryService;
        $this->fileService = $fileService;
        $this->nfnPanoptesExport = $nfnPanoptesExport;
    }

    /**
     * @param StagedQueueContract $contract
     */
    public function handle(StagedQueueContract $contract)
    {
        $queue = $contract->setCacheLifetime(0)->findByIdWithExpeditionActor(1, 17, 2);

        //$this->nfnPanoptesExport->images($queue);

        $this->nfnPanoptesExport->convert($queue);

    }
}

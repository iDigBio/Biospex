<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Google\FusionTableService;
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
     * @var ProjectContract
     */
    private $projectContract;
    /**
     * @var TranscriptionLocationContract
     */
    private $transcriptionLocationContract;
    /**
     * @var FusionTableService
     */
    private $fusionTableService;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(ProjectContract $projectContract, TranscriptionLocationContract $transcriptionLocationContract, FusionTableService $fusionTableService)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
        $this->transcriptionLocationContract = $transcriptionLocationContract;
        $this->fusionTableService = $fusionTableService;
    }

    /**
     *
     */
    public function handle()
    {
        echo 'Sending to job' . PHP_EOL;

        $job = new NfnClassificationsFusionTableJob();
        $job->handle($this->projectContract, $this->transcriptionLocationContract, $this->fusionTableService);

        echo 'Finished' . PHP_EOL;
    }
}

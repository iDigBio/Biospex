<?php

namespace App\Console\Commands;


use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Google\Bucket;
use App\Services\Google\Drive;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Services\Google\Table;

class TestAppCommand extends Command
{

    use DispatchesJobs;
    public $projectContract;


    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var NfnClassificationsFusionTableJob
     */
    private $job;
    /**
     * @var TranscriptionLocationContract
     */
    private $locationContract;
    /**
     * @var Table
     */
    private $table;
    /**
     * @var Bucket
     */
    private $bucket;
    /**
     * @var Drive
     */
    private $drive;


    /**
     * TestAppCommand constructor.
     */
    public function __construct(
        ProjectContract $projectContract,
        TranscriptionLocationContract $locationContract,
        Table $table,
        Bucket $bucket,
        Drive $drive
    )
    {
        parent::__construct();

        $this->projectContract = $projectContract;
        $this->locationContract = $locationContract;
        $this->table = $table;
        $this->bucket = $bucket;
        $this->drive = $drive;
    }

    public function handle()
    {
        $ids = [13,15,16,17,18,26,31,33];

        $this->job = new NfnClassificationsFusionTableJob($ids);
        $this->job->handle(
            $this->projectContract,
            $this->locationContract,
            $this->table,
            $this->bucket,
            $this->drive
        );
    }

}

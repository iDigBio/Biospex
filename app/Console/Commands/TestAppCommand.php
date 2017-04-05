<?php

namespace App\Console\Commands;


use App\Exceptions\Handler;
use App\Jobs\NfnClassificationsCsvFileJob;
use App\Jobs\NfnClassificationsFusionTableJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\Api\NfnApi;
use App\Services\Google\Bucket;
use App\Services\Google\Drive;
use App\Services\Report\Report;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Services\Google\Table;
use PulkitJalan\Google\Facades\Google;

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
     * TestAppCommand constructor.
     */
    public function __construct(

    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $tableService = app(Table::class);

        dd($tableService->listTableStyle('1Cn8Te7Bbcqla5E9Cki1oS30Zp98Zz3k7YZua6pbm'));
    }

}

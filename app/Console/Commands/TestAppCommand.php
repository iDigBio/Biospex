<?php

namespace App\Console\Commands;


use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\StateCountyContract;
use App\Repositories\Contracts\TranscriptionLocationContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use PulkitJalan\Google\Facades\Google;
use App\Services\Csv\Csv;
use App\Services\Google\Table;

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
     * TestAppCommand constructor.
     */
    public function __construct(
    )
    {
        parent::__construct();

    }

}

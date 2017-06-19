<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Contracts\SubjectContract;
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
     * TestAppCommand constructor.
     */
    public function __construct(

    )
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle(SubjectContract $subjectContract)
    {
        $count = $subjectContract->setCacheLifetime(0)->getUnassignedCount(34);
        dd($count);
    }
}

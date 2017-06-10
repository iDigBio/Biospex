<?php

namespace App\Console\Commands;

use App\Jobs\ExportQueueJob;
use App\Models\Actor;
use App\Models\Expedition;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ExportQueueContract;
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
     * TestAppCommand constructor.
     */
    public function __construct(

    )
    {
        parent::__construct();
    }

    /**
     * @param ExportQueueContract $exportQueueContract
     */
    public function handle(ExportQueueContract $exportQueueContract)
    {
        \DB::listen(function ($query) {
            echo$query->sql . PHP_EOL;
            // $query->bindings
            // $query->time
        });
        $entity = $exportQueueContract->setCacheLifetime(0)->getFirst();
        //$this->dispatch((new ExportQueueJob($entity))->onQueue(config('config.beanstalkd.export')));
    }
}

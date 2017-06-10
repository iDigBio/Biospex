<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\ImportContract;
use Illuminate\Support\Facades\Queue;

class DarwinCoreFileImportCommand extends Command
{

    /**
     * @var ImportContract
     */
    private $importContract;

    /**
     * @var mixed
     */
    private $tube;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'dwc:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to re-queue dwc import after a failure.";

    /**
     * DarwinCoreFileImportCommand constructor.
     * 
     * @param ImportContract $importContract
     */
    public function __construct(ImportContract $importContract)
    {
        parent::__construct();

        $this->importContract = $importContract;
        $this->tube = config('config.beanstalkd.import');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $imports = $this->importContract->setCacheLifetime(0)->findWhere(['error', '=', 0]);

        $count = 0;
        foreach ($imports as $import) {
            Queue::push('App\Services\Queue\DarwinCoreFileImportQueue', ['id' => $import->id], $this->tube);
            $count++;
        }

        echo $count . " Imports added to Queue." . PHP_EOL;

    }
}

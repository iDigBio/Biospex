<?php

namespace App\Console\Commands;

use App\Jobs\DwcFileImportJob;
use Illuminate\Console\Command;
use App\Repositories\Interfaces\Import;

class DarwinCoreFileImportCommand extends Command
{

    /**
     * @var Import
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
     * @param Import $importContract
     */
    public function __construct(Import $importContract)
    {
        parent::__construct();

        $this->importContract = $importContract;
        $this->tube = config('config.beanstalkd.import_tube');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $import = $this->importContract->getFirstImportWithoutError();

        if ($import === null)
            return;

        DwcFileImportJob::dispatch($import);

        echo "Import added to Queue." . PHP_EOL;

    }
}

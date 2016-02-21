<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\Import;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

class DarwinCoreFileImportCommand extends Command
{
    public $import;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'dwc:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to re-queue dwc import after a failure.";

    /**
     * Class constructor.
     *
     * @param ImportInterface $import
     */
    public function __construct(Import $import)
    {
        parent::__construct();

        $this->import = $import;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $imports = $this->import->findByError();

        $count = 0;
        foreach ($imports as $import) {
            Queue::push('App\Services\Queue\DarwinCoreFileImportQueue', ['id' => $import->id], Config::get('config.beanstalkd.import'));
            $count++;
        }

        echo $count . " Imports added to Queue." . PHP_EOL;

        return;
    }
}

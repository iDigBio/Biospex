<?php

namespace App\Console\Commands;

use App\Services\Actor\NfnPanoptesExportBatch;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Actor\NfnPanoptesExportBatch
     */
    private $batch;

    /**
     * AppCommand constructor.
     */
    public function __construct(NfnPanoptesExportBatch $batch) {
        parent::__construct();
        $this->batch = $batch;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $download = $this->batch->getDownload(806);

        $this->batch->process($download);
    }
}
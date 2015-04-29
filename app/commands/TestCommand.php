<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Biospex\Services\Report\SubjectImportReport as Report;


class TestCommand extends Command {

    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Constructor
     */
    public function __construct(Report $report)
    {
        parent::__construct();

        $this->report = $report;

    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $this->report->complete('biospex@gmail.com', 'This is a test.', ['duplicates'], ['reject']);

        return;
    }
}

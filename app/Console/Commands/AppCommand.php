<?php

namespace App\Console\Commands;

use App\Models\OcrQueue;
use App\Services\Process\OcrService;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * @var \App\Services\Process\OcrService
     */
    private $service;

    /**
     * AppCommand constructor.
     */
    public function __construct(OcrService $service) {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $count = $this->service->getSubjectCount(15);
        dd($count);
    }

}
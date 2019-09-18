<?php

namespace App\Console\Commands;

use App\Services\Api\NfnApiService;
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
     * @var \App\Services\Api\NfnApiService
     */
    private $service;

    /**
     * AppCommand constructor.
     *
     * @param \App\Services\Api\NfnApiService $service
     */
    public function __construct(NfnApiService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $project = $this->service->getNfnProject(9275);
        dd($project['slug']);
    }

}
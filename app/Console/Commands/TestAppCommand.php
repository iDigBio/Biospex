<?php

namespace App\Console\Commands;

use App\Services\Actor\ActorService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var ActorService
     */
    private $service;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(ActorService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the Job.
     */
    public function fire()
    {
        $vars = [
            'title'          => 'This is a test',
            'message'        => trans('emails.expedition_export_complete_message', ['expedition' => 'This Expedition']),
            'groupId'        => 1,
            'attachmentName' => trans('emails.missing_images_attachment_name', ['recordId' => '100'])
        ];

        $this->service->processComplete($vars);
    }
}

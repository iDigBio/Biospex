<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use App\Repositories\Contracts\OcrQueue;

class OcrQueuePushCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ocrqueue:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Push ocr job to queue and run.";

    /**
     * Class constructor
     *
     * @param OcrQueueInterface $repo
     */
    public function __construct(OcrQueue $repo)
    {
        parent::__construct();

        $this->repo = $repo;
        $this->queue = \Config::get('config.beanstalkd.ocr');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // Get the name arguments and the age option from the input instance.
        $id = $this->argument('id');

        // Retrieve record and update error column if needed
        $job = $this->repo->find($id);

        if (empty($job))
        {
            echo "Cannot retrieve ocr queued job from table." . PHP_EOL;

            return;
        }

        if ($job->error)
        {
            $job->error = 0;
            $job->save();
        }

        // Push to queue
        \Queue::push('App\Services\Queue\QueueFactory', ['id' => $job->id, 'class' => 'OcrProcessQueue'], $this->queue);

        return;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['id', InputArgument::REQUIRED, 'Id of job from ocr_queue table.'],
        ];
    }
}

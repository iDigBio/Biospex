<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use App\Interfaces\OcrQueue;

class OcrQueuePushCommand extends Command
{

    /**
     * @var OcrQueue
     */
    public $ocrQueueContract;

    /**
     * @var
     */
    public $tube;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ocrqueue:push {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Push ocr job to queue and run.";

    /**
     * OcrQueuePushCommand constructor.
     * 
     * @param OcrQueue $ocrQueueContract
     */
    public function __construct(OcrQueue $ocrQueueContract)
    {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->tube = config('config.beanstalkd.ocr');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get the name arguments and the age option from the input instance.
        $id = $this->argument('id');

        // Retrieve record and update error column if needed
        $job = $this->ocrQueueContract->find($id);

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
        Queue::push('App\Services\Queue\OcrProcessQueue', ['id' => $job->id], $this->tube);

    }

}

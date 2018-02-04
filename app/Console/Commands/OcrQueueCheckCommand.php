<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\OcrQueue;
use App\Repositories\Interfaces\User;
use App\Notifications\OcrQueueCheck;
use Illuminate\Console\Command;

class OcrQueueCheckCommand extends Command
{

    /**
     * @var OcrQueue
     */
    private $queueContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ocrqueue:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check ocr queue table for invalid records";

    /**
     * OcrQueueCheckCommand constructor.
     *
     * @param OcrQueue $queueContract
     * @param User $userContract
     */
    public function __construct(OcrQueue $queueContract, User $userContract)
    {
        parent::__construct();

        $this->queueContract = $queueContract;
        $this->userContract = $userContract;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $queues = $this->queueContract->all();

        if ($queues->isEmpty()) {
            return;
        }

        $message = '';
        foreach ($queues as $queue) {
            $message .= (trans('errors.ocr_queue_check',
                [
                    'id'      => $queue->id,
                    'message' => trans('errors.ocr_stuck_queue', ['id' => $queue->id]),
                    'url'     => ''
                ]));
        }

        $user = $this->userContract->find(1);

        $user->notify(new OcrQueueCheck($message));
    }
}

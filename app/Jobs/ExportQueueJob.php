<?php

namespace App\Jobs;

use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\ExportQueueContract;
use App\Repositories\Contracts\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportQueueJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     * @param ExportQueueContract $exportQueueContract
     */
    public function handle(ExportQueueContract $exportQueueContract)
    {

    }
}

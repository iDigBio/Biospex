<?php

namespace App\Jobs;

use App\Services\Model\WeDigBioDashboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class NfnClassificationWeDigBioJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = json_decode($data);
        $this->onQueue(config('config.beanstalkd.nfnpusher'));
    }

    /**
     * Execute the job.
     *
     * @param WeDigBioDashboardService $service
     * @return void
     */
    public function handle(WeDigBioDashboardService $service)
    {
        if ( ! isset($this->data->workflow_id))
        {
            $this->delete();
            return;
        }

        $service->processDataFromPusher($this->data);

        $this->delete();

        return;
    }
}

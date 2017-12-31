<?php

namespace App\Jobs;

use App\Services\Model\WeDigBioDashboardService;

class NfnClassificationWeDigBioJob extends Job
{
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

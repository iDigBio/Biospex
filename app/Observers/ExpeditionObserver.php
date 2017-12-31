<?php

namespace App\Observers;

use App\Jobs\BuildExpeditionOcrFile;
use App\Models\Expedition as Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExpeditionObserver
{
    use DispatchesJobs;

    /**
     * Expedition created.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        $this->dispatch((new BuildExpeditionOcrFile($model))->onQueue(config('config.beanstalkd.default')));
    }

    /**
     * Expedition updated.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        $this->dispatch((new BuildExpeditionOcrFile($model))->onQueue(config('config.beanstalkd.default')));
    }
}
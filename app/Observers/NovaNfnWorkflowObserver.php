<?php

namespace App\Observers;

use App\Jobs\UpdateNfnWorkflowJob;
use App\Models\NfnWorkflow as Model;

class NovaNfnWorkflowObserver
{
    /**
     * Entity Updated.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        //UpdateNfnWorkflowJob::dispatch($model);
    }

    /**
     * Entity Updated.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        //UpdateNfnWorkflowJob::dispatch($model);
    }
}

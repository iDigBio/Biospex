<?php

namespace App\Observers;

use App\Jobs\PanoptesProjectUpdateJob;
use App\Models\PanoptesProject as Model;

class NovaPanoptesProjectObserver
{
    /**
     * Entity Updated.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        PanoptesProjectUpdateJob::dispatch($model);
    }

    /**
     * Entity Updated.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
        PanoptesProjectUpdateJob::dispatch($model);
    }
}

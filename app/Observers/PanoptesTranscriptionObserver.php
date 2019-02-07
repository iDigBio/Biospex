<?php

namespace App\Observers;

use App\Models\PanoptesTranscription as Model;
use Cache;

class PanoptesTranscriptionObserver
{
    /**
     * Model created.
     *
     * @param \App\Models\PanoptesTranscription $model
     */
    public function created(Model $model)
    {
        Cache::tags(['panoptes' . $model->subject_projectId])->flush();
    }

    /**
     * Model updated.
     *
     * @param \App\Models\PanoptesTranscription $model
     */
    public function updated(Model $model)
    {
        Cache::tags(['panoptes' . $model->subject_projectId])->flush();
    }

    /**
     * Model deleted.
     *
     * @param \App\Models\PanoptesTranscription $model
     */
    public function deleted(Model $model)
    {
        Cache::tags(['panoptes' . $model->subject_projectId])->flush();
    }
}
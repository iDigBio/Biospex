<?php

namespace App\Observers;

use App\Models\Subject as Model;
use Cache;

class SubjectObserver
{
    /**
     * Model created.
     *
     * @param \App\Models\Subject $model
     */
    public function created(Model $model)
    {
        \Log::alert('created subject model');
        Cache::tags(['subjects' . $model->project_id])->flush();
    }

    /**
     * Model updated.
     *
     * @param \App\Models\Subject $model
     */
    public function updated(Model $model)
    {
        \Log::alert('updated subject model');
        Cache::tags(['subjects' . $model->project_id])->flush();
    }

    /**
     * Model deleted.
     *
     * @param \App\Models\Subject $model
     */
    public function deleted(Model $model)
    {
        \Log::alert('deleted subject model');
        Cache::tags(['subjects' . $model->project_id])->flush();
    }
}
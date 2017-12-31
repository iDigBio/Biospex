<?php

namespace App\Observers;

use App\Models\PanoptesTranscription as Model;
use Cache;

class PanoptesTranscriptionObserver
{
    /**
     * Expedition created.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
        Cache::tags(['panoptesTranscriptions'])->flush();
    }

    /**
     * Expedition updated.
     *
     * @param Model $model
     */
    public function entityUpdated(Model $model)
    {
        Cache::tags(['panoptesTranscriptions'])->flush();
    }
}
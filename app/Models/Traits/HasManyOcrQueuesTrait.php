<?php namespace App\Models\Traits;

trait HasManyOcrQueuesTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrQueues()
    {
        return $this->hasMany('App\Models\OcrQueue');
    }
}

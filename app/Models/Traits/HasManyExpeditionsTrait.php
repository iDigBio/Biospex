<?php namespace App\Models\Traits;

trait HasManyExpeditionsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions()
    {
        return $this->hasMany('App\Models\Expedition');
    }
}

<?php namespace App\Models\Traits;

trait EmbedsOneOccurrenceTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\EmbedsMany
     */
    public function occurrence()
    {
        return $this->embedsOne('App\Models\Occurrence', 'occurrence');
    }
}

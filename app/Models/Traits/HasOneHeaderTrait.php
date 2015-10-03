<?php namespace App\Models\Traits;

trait HasOneHeaderTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function header()
    {
        return $this->hasOne('App\Models\Header');
    }
}

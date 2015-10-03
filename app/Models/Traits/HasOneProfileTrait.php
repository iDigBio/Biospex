<?php namespace App\Models\Traits;

trait HasOneProfileTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }
}

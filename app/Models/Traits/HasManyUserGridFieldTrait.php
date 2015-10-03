<?php namespace App\Models\Traits;

trait HasManyUserGridFieldTrait
{
    /**
     * Return projects owned by the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userGridField()
    {
        return $this->hasMany('App\Models\UserGridField');
    }
}

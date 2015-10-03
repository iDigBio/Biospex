<?php namespace App\Models\Traits;

trait HasManyMetasTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metas()
    {
        return $this->hasMany('App\Models\Meta');
    }
}

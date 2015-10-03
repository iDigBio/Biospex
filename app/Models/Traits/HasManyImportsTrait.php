<?php namespace App\Models\Traits;

trait HasManyImportsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imports()
    {
        return $this->hasMany('App\Models\Import');
    }
}

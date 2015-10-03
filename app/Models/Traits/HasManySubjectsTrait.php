<?php namespace App\Models\Traits;

trait HasManySubjectsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subjects()
    {
        return $this->hasMany('App\Models\Subject');
    }
}

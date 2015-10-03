<?php namespace App\Models\Traits;

use App\Models\Project;

trait HasManyProjectsTrait
{
    /**
     * Return projects owned by the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class)->orderBy('title');
    }
}

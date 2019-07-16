<?php 

namespace App\Models;

class Workflow extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'workflows';

    /**
     * @inheritDoc
     */
    protected $fillable = ['title', 'enabled'];

    /**
     * @return mixed
     */
    public function actors()
    {
        return $this->belongsToMany(Actor::class)->withPivot('order')->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function project()
    {
        return $this->hasMany(Project::class);
    }
}
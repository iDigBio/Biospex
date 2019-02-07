<?php

namespace App\Models;

class TeamCategory extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'team_categories';

    /**
     * @inhritDoc
     */
    protected $fillable = ['name'];

    /**
     * Faq relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
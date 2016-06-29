<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'team_categories';

    protected $fillable = [
        'name',
        'label'
    ];

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
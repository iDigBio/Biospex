<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class TeamCategory extends Model
{

    use LadaCacheTrait;

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
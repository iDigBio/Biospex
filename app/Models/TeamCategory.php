<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class TeamCategory extends Model
{

    use LadaCacheTrait, SoftCascadeTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'team_categories';

    /**
     * @inhritDoc
     */
    protected $fillable = ['name'];

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = [
        'teams'
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
<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\TeamPresenter;

class Team extends BaseEloquentModel
{

    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'teams';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'team_category_id',
        'first_name',
        'last_name',
        'email',
        'institution'
    ];

    /**
     * @var string
     */
    protected $presenter = TeamPresenter::class;

    /**
     * TeamCategory relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teamCategory()
    {
        return $this->belongsTo(TeamCategory::class);
    }

    /**
     * Get full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }
}
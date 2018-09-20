<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\TeamPresenter;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Team extends Model
{

    use LadaCacheTrait, Presentable;

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
    public function category()
    {
        return $this->belongsTo(TeamCategory::class);
    }
}
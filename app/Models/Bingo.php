<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\BingoPresenter;

class Bingo extends BaseEloquentModel
{
    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'bingos';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'directions'
    ];

    /**
     * @var string
     */
    protected $presenter = BingoPresenter::class;

    /**
     * User relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function words()
    {
        return $this->hasMany(BingoWord::class);
    }
}

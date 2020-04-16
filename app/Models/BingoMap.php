<?php

namespace App\Models;

class BingoMap extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'bingo_maps';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'bingo_id',
        'ip',
        'latitude',
        'longitude',
        'city',
        'winner'
    ];

    /**
     * Bingo relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }
}

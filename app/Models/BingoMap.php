<?php

namespace App\Models;

use App\Models\Traits\UuidTrait;

class BingoMap extends BaseEloquentModel
{
    use UuidTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'bingo_maps';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'bingo_id',
        'uuid',
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

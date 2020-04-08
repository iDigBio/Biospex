<?php

namespace App\Models;

class BingoWord extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'bingo_words';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'word'
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

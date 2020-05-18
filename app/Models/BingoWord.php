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
        'bingo_id',
        'word',
        'definition'
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

    /**
     * Accessor for ip.
     *
     * @param $value
     * @return false|string
     */
    public function getIpAttribute($value)
    {
        return inet_ntop($value);
    }

    /**
     * Mutator for ip.
     *
     * @param $value
     */
    public function setIpAttribute($value)
    {
        $this->attributes['ip_address'] = inet_pton($value);
    }
}

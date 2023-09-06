<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoLocateForm extends BaseEloquentModel
{
    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $table = 'geo_locate_forms';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'group_id',
        'name',
        'source',
        'hash',
        'fields'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'fields' => 'array'
    ];

    /**
     * Group relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expeditions(): HasMany
    {
        return $this->hasMany(Expedition::class);
    }
}

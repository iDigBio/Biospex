<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoLocateForm extends BaseEloquentModel
{
    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @inheritDoc
     */
    protected $table = 'geolocate_forms';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'expedition_id',
        'file_path',
        'properties'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'properties' => 'array'
    ];

    /**
     * Expedition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }
}

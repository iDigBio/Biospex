<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoLocate extends BaseMongoModel
{
    /**
     * Set Collection
     */
    protected $collection = 'geolocates';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subject_id'           => 'integer',
        'subject_expeditionId' => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /**
     * Expdition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expedition::class, 'subject_expeditionId', 'id');
    }
}

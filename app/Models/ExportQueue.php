<?php

namespace App\Models;

class ExportQueue extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'export_queues';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'expedition_id',
        'state',
        'queued',
        'missing'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Set queued attribute.
     *
     * @param $value
     * @return int
     */
    public function getQueuedAttribute($value)
    {
        return $value === null ? 0 : $value;
    }

    /**
     * Set state attribute.
     *
     * @param $value
     * @return int
     */
    public function getStateAttribute($value)
    {
        return $value === null ? 0 : $value;
    }
}

<?php

namespace App\Models;

class OcrQueue extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'ocr_queues';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'expedition_id',
        'mongo_id',
        'total',
        'processed',
        'status',
        'queued',
        'error',
        'csv'
    ];

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
     * Expedition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Get csv attribute.
     *
     * @param $value
     * @return mixed
     */
    public function getCsvAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Set csv attribute.
     *
     * @param $value
     */
    public function setCsvAttribute($value)
    {
        $this->attributes['csv'] = serialize($value);
    }
}

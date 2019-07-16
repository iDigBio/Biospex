<?php

namespace App\Models;

use App\Presenters\OcrQueuePresenter;

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
        'total',
        'processed',
        'status',
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
     * OcrFile Relation
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ocrFiles()
    {
        return $this->hasMany(OcrFile::class, 'queue_id');
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

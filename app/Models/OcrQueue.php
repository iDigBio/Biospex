<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;

class OcrQueue extends Model
{
    use UuidTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'ocr_queues';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'ocr_csv_id',
        'uuid',
        'data',
        'total',
        'processed',
        'tries',
        'batch',
        'status',
        'error',
        'attachments'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ocrCsv()
    {
        return $this->belongsTo(OcrCsv::class);
    }

    /**
     * Mutator for subjects column.
     *
     * @param $value
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }

    /**
     * Accessor for subjects column.
     *
     * @param $value
     * @return mixed
     */
    public function getDataAttribute($value)
    {
        return empty($value) ? '' : unserialize($value);
    }
}

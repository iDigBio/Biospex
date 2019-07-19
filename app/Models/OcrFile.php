<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class OcrFile extends Model
{
    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'ocr_files';

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'queue_id',
        'subject_id',
        'messages',
        'ocr',
        'status',
        'url'
    ];

    /**
     * OCrQueue relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ocrQueue()
    {
        return $this->belongsTo(OcrQueue::class);
    }
}
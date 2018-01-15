<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidTrait;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class OcrQueue extends Model
{
    use UuidTrait, LadaCacheTrait;

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
}

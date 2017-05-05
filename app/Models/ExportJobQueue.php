<?php

namespace App\Models;

class ExportJobQueue extends BaseEloquentModel
{

    /**
     * @inheritDoc
     */
    protected $table = 'export_job_queues';

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
}

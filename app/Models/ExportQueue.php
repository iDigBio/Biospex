<?php

namespace App\Models;


class ExportQueue extends BaseEloquentModel
{
    /**
     * @ineritDoc
     */
    protected $table = 'export_queues';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'expedition_id',
        'actor_id',
        'stage',
        'queued',
        'missing'
    ];

}
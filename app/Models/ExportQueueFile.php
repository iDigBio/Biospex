<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;

class ExportQueueFile extends BaseEloquentModel
{
    use HybridRelations;

    /**
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * @ineritDoc
     */
    protected $table = 'export_queue_files';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'queue_id',
        'subject_id',
        'url',
        'error',
        'error_message'
    ];

    /**
     * ExportQueue relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function queue()
    {
        return $this->belongsTo(ExportQueue::class);
    }

    /**
     * Subject relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subject()
    {
        return $this->hasOne(Subject::class, '_id', 'subject_id');
    }
}
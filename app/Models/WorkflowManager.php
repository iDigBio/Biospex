<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class WorkflowManager extends Model
{
    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'workflow_managers';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'expedition_id',
        'stopped'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Scope
     *
     * @param $query
     * @param $expeditionId
     * @return mixed
     */
    public function scopeExpeditionId($query, $expeditionId)
    {
        return $query->where('expedition_id', '=', $expeditionId);
    }
}

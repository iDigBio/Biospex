<?php

namespace App\Models;


class WorkflowManager extends BaseEloquentModel
{
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

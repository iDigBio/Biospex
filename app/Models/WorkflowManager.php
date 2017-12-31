<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class WorkflowManager extends Model
{
    use SoftDeletes, LadaCacheTrait;

    /**
     * Enable soft delete.
     *
     * @var boolean
     */
    protected $softDelete = true;

    /**
     * @inheritDoc
     */
    protected $table = 'workflow_managers';

    /**
     * @inheritDoc
     */
    protected $dates = ['deleted_at'];

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
     * @param $id
     * @return mixed
     */
    public function scopeExpeditionId($query, $id)
    {
        return $query->where('expedition_id', '=', $id);
    }
}

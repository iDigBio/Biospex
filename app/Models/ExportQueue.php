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
        'batch',
        'count',
        'error',
        'missing'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * ExportQueueFiles relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(ExportQueueFile::class);
    }

    /**
     * Mutator for missing column.
     *
     * @param $value
     */
    public function setMissingAttribute($value)
    {
        $this->attributes['missing'] = serialize($value);
    }

    /**
     * Accessor for missing column.
     *
     * @param $value
     * @return mixed
     */
    public function getMissingAttribute($value)
    {
        return empty($value) ? [] : unserialize($value);
    }
}
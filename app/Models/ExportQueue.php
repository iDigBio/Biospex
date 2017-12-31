<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ExportQueue extends Model
{
    use LadaCacheTrait;

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
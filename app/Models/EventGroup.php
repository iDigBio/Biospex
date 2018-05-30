<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;
use App\Models\Traits\UuidTrait;

class EventGroup extends Model
{
    use LadaCacheTrait, UuidTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'event_groups';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'title'
    ];

    /**
     * Event relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * EventUser relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(EventUser::class, 'event_group_user', 'group_id', 'user_id');
    }

    /**
     * Event transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptions()
    {
        return $this->hasMany(EventTranscription::class, 'group_id');
    }
}

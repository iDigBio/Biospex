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
        return $this->hasMany(EventTranscription::class);
    }

    /**
     * Transcription count relationship.
     *
     * \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transcriptionCount()
    {
        return $this->hasOne(EventTranscription::class, 'group_id')
            ->selectRaw('group_id, count(*) as aggregate')
            ->groupBy('group_id');
    }

    /**
     * Transcription count attribute.
     *
     * @return int
     */
    public function getTranscriptionCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if ( ! $this->relationLoaded('transcriptionCount'))
            $this->load('transcriptionCount');

        $related = $this->getRelation('transcriptionCount');

        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }
}

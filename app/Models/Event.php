<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Event extends Model
{
    use LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'events';

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'start_date',
        'end_date'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'project_id' => 'integer',
        'owner_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'contact' => 'string',
        'contact_email' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'timezone' => 'string'
    ];

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'project_id',
        'owner_id',
        'title',
        'description',
        'contact',
        'contact_email',
        'start_date',
        'end_date',
        'timezone'
    ];

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Owner relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * EventGroup relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(EventGroup::class);
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
        return $this->hasOne(EventTranscription::class, 'event_id')
            ->selectRaw('event_id, count(*) as aggregate')
            ->groupBy('event_id');
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

    /**
     * Set start date attribute.
     *
     * @param $value
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value . ':00';
    }

    /**
     * Set end date attribute.
     *
     * @param $value
     */
    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value . ':00';
    }
}

<?php

namespace App\Models;

use App\Models\Traits\Presentable;
use App\Presenters\EventPresenter;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Event extends Model
{
    use LadaCacheTrait, Presentable;

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
     * @var string
     */
    protected $presenter = EventPresenter::class;

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
     * EventTeam relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(EventTeam::class);
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
     * Set start date attribute.
     *
     * @param $value
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value->setTimezone(new \DateTimeZone(config('app.timezone')));
    }

    /**
     * Set end date attribute.
     *
     * @param $value
     */
    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value->setTimezone(new \DateTimeZone(config('app.timezone')));
    }
}

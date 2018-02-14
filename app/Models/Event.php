<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Event extends Model
{
    use LadaCacheTrait, SoftCascadeTrait;

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
    ];

    /**
     * Soft delete cascades.
     *
     * @var array
     */
    protected $softCascade = ['groups'];

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
     * EventUser Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->hasManyThrough(EventUser::class,EventGroup::class);
    }

    /**
     * Set start date attribute.
     *
     * @param $value
     * @return string
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = $value . ':00';
    }

    /**
     * Set end date attribute.
     *
     * @param $value
     * @return string
     */
    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = $value . ':00';
    }
}

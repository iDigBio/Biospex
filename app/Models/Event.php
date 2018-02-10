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
        return $this->hasMany(EventGroup::class, 'event_group_user');
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
}

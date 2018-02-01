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
        'owner_id',
        'title',
        'description',
        'contact',
        'contact_email',
        'start_date',
        'end_date',
    ];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(EventGroup::class, 'event_group_user');
    }

    /**
     * EventUser Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(EventUser::class, 'event_group_user');
    }
}

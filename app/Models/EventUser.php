<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class EventUser extends Model
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
        'nfn_user'
    ];

    /**
     * Events relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function events()
    {
        return $this->hasManyThrough(Event::class, EventGroup::class);
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
}

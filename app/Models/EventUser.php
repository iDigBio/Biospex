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
    protected $table = 'event_users';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'nfn_user'
    ];

    /**
     * EventGroup relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(EventGroup::class, 'event_group_user', 'user_id', 'group_id')
            ->withPivot('group_id', 'user_id');
    }

    /**
     * Event transcription relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcriptions()
    {
        return $this->hasMany(EventTranscription::class, 'user_id');
    }
}

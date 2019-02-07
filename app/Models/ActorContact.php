<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class ActorContact extends BaseEloquentModel
{
    use Notifiable;

    /**
     * @inheritDoc
     */
    protected $table = 'actor_contacts';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'email',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }
}

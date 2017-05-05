<?php

namespace App\Models;


class ActorContact extends BaseEloquentModel
{

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

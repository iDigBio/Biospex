<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActorContact extends Model
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

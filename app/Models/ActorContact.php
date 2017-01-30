<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActorContact extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'actor_contacts';

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

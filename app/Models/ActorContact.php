<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ActorContact extends Model
{
    use Notifiable, LadaCacheTrait;

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class Invite extends Model
{
    use Notifiable, LadaCacheTrait;

    /**
     * @inheritDoc
     */
    protected $table = 'invites';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'group_id',
        'email',
        'code'
    ];

    /**
     * Group relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

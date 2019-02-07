<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class Invite extends BaseEloquentModel
{
    use Notifiable;

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

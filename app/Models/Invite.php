<?php

namespace App\Models;

class Invite extends BaseEloquentModel
{

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

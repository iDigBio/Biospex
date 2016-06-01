<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invites';

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

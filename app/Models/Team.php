<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

    /**
     * @inheritDoc
     */
    protected $table = 'teams';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'team_category_id',
        'first_name',
        'last_name',
        'email',
        'institution'
    ];

    /**
     * TeamCategory relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(TeamCategory::class);
    }

    /**
     * Get full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }
}
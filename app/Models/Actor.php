<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Actor
 */
class Actor extends BaseEloquentModel
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'actors';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'title',
        'url',
        'class',
    ];

    /**
     * Scope for active.
     */
    public function scopeActive($query): mixed
    {
        return $query->where('active', 1);
    }

    /**
     * Workflows relationship.
     */
    public function workflows(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Workflow::class)->using(ActorWorkflow::class);
    }

    /**
     * Download relationship.
     */
    public function downloads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActorContact::class);
    }

    /**
     * Expedition relationship.
     */
    public function expeditions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Expedition::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Expedition::class, ActorExpedition::class, 'actor_id', 'id', 'id', 'expedition_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exportQueues()
    {
        return $this->hasMany(ExportQueue::class);
    }
}

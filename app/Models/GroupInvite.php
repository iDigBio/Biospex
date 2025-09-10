<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use App\Models\Traits\UuidTrait;
use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class GroupInvite extends BaseEloquentModel
{
    use Cacheable, HasFactory, Notifiable, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'group_invites';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'group_id',
        'email',
        'code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['group'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot functions.
     */
    public static function boot()
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\GroupInviteFactory::new();
    }

    /**
     * Group relationship.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

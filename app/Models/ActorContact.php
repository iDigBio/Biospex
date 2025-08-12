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

use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * Class ActorContact
 *
 * Represents contact information for actors within the application system.
 * This model stores email addresses and other contact details for actors,
 * enabling communication and notification functionality.
 *
 * Key Features:
 * - Supports caching for improved performance
 * - Implements notification functionality via Notifiable trait
 * - Maintains relationship with Actor model
 * - Stores contact information for actor communication
 */
class ActorContact extends BaseEloquentModel
{
    use Cacheable, HasFactory, Notifiable;

    /**
     * The name of the database table associated with the model.
     *
     * @var string
     */
    protected $table = 'actor_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'email',    // The email address for the actor contact
    ];

    /**
     * Get the relations that should be cached for performance optimization.
     *
     * This method defines which Eloquent relationships should be cached
     * when using the Cacheable trait to improve query performance.
     *
     * @return array<string> Array of relationship names to be cached
     */
    protected function getCacheRelations(): array
    {
        return ['actor'];
    }

    /**
     * Define a many-to-one relationship with the Actor model.
     *
     * An ActorContact belongs to a single Actor, representing the contact
     * information associated with that specific actor.
     */
    public function actor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}

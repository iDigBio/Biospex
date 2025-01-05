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

use App\Models\Traits\Presentable;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Profile
 */
class Profile extends BaseEloquentModel implements AttachableInterface
{
    use HasFactory, PaperclipTrait, Presentable;

    /**
     * {@inheritDoc}
     */
    protected $table = 'profiles';

    /**
     * The attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'avatar_updated_at' => 'datetime',
            'avatar_created_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'timezone',
        'avatar',
    ];

    /**
     * Profile constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar', [
            'variants' => [
                'medium' => '160x160',
                'small' => '25x25',
            ],
            'url' => config('config.missing_avatar_medium'),
            'urls' => [
                'small' => config('config.missing_avatar_small'),
                'medium' => config('config.missing_avatar_medium'),
            ],
        ]);

        parent::__construct($attributes);
    }

    /**
     * Boot function to add model events
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

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
use App\Models\Traits\UuidTrait;
use App\Presenters\DownloadPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Download
 */
class Download extends BaseEloquentModel
{
    use HasFactory, Presentable, UuidTrait;

    /**
     * {@inheritDoc}
     */
    protected $table = 'downloads';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'uuid',
        'expedition_id',
        'actor_id',
        'file',
        'type',
        'updated_at',
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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @var string
     */
    protected $presenter = DownloadPresenter::class;

    /**
     * Model Boot
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();

    }

    /**
     * Expedition relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Actor relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor()
    {
        return $this->belongsTo(Actor::class);
    }
}

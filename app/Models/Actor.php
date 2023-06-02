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
use App\Presenters\ActorPresenter;

/**
 * Class Actor
 *
 * @package App\Models
 */
class Actor extends BaseEloquentModel
{
    use Presentable;

    /**
     * @inheritDoc
     */
    protected $table = 'actors';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'title',
        'url',
        'class'
    ];

    /**
     * @var string
     */
    protected $presenter = ActorPresenter::class;

    /**
     * Scope for active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query): mixed
    {
        return $query->where('active', 1);
    }

    /**
     * Workflow relationship.
     *
     * @return mixed
     */
    public function workflows()
    {
        return $this->belongsToMany(Workflow::class)->using(ActorWorkflow::class);
    }

    /**
     * Download relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(ActorContact::class);
    }

    /**
     * Expedition relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expeditions()
    {
        return $this->belongsToMany(Expedition::class, 'actor_expedition')
            ->withPivot('id', 'expedition_id', 'actor_id', 'state', 'total', 'error', 'order', 'expert')
            ->orderBy('order')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exportQueues()
    {
        return $this->hasMany(ExportQueue::class);
    }
}

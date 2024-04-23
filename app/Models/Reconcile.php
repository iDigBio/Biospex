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
use App\Presenters\ReconcilePresenter;

/**
 * Class Reconcile
 *
 * @package App\Models
 */
class Reconcile extends BaseMongoModel
{
    use Presentable;

    /**
     * Set Collection
     */
    protected $collection = 'reconciles';

    /**
     * @inheritDoc
     */
    protected $dates = ['created_at', 'updated_at', 'timestamp'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subject_id' => 'integer',
        'subject_projectId' => 'integer',
        'subject_expeditionId' => 'integer',
        'problem' => 'integer'
    ];

    /**
     * @var string
     */
    protected $presenter = ReconcilePresenter::class;

    /**
     * Project relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\Mongodb\Laravel\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Expdition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expedition()
    {
        return $this->belongsTo(Expedition::class, 'expedition_id', 'id');
    }

    /**
     * Panoptes Transcripition relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Mongodb\Laravel\Relations\HasMany
     */
    public function transcriptions()
    {
        return $this->hasMany(PanoptesTranscription::class, 'subject_id', 'subject_id');
    }

}

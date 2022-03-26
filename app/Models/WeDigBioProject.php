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

/**
 * Class WeDigBioProject
 *
 * @package App\Models
 */
class WeDigBioProject extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'wedigbio_projects';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'panoptes_project_id',
        'panoptes_workflow_id',
        'subject_sets',
    ];

    /**
     * Mutator for subject_sets column.
     *
     * @param $value
     */
    public function setSubjectSetsAttribute($value)
    {
        $this->attributes['subject_sets'] = json_encode($value);
    }

    /**
     * Accessor for subjects column.
     *
     * @param $value
     * @return mixed
     */
    public function getSubjectSetsAttribute($value)
    {
        return json_decode($value);
    }

}

<?php
/**
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

namespace App\Repositories\Eloquent;

use App\Models\TeamCategory as Model;
use App\Repositories\Interfaces\TeamCategory;

class TeamCategoryRepository extends EloquentRepository implements TeamCategory
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getTeamIndexPage()
    {
        return $this->model
            ->with('teams')
            ->orderBy('id', 'asc')
            ->groupBy('id')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getTeamCategorySelect()
    {
        return $this->model->pluck('name', 'id')->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getCategoriesWithTeams()
    {
        return $this->model->with('teams')->groupBy('id')->get();
    }
}
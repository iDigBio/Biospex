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

use App\Models\EventUser as Model;
use App\Repositories\Interfaces\EventUser;

class EventUserRepository extends EloquentRepository implements EventUser
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
     * Get nfn user by name.
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function getEventUserByName($name)
    {
        return $this->model->where('nfn_user', $name)->first(['id']);
    }

    public function getEventsByUser(string $user, int $projectId, string $finishedDate)
    {
        return $this->model->whereHas(['team' => function($q) use($projectId, $finishedDate) {
            $q->whereHas(['event' => function($q2) use($projectId, $finishedDate) {
                $q2->where('project_id', $projectId)
                    ->where('start_date', '<', $finishedDate)
                    ->where('end_date', '>', $finishedDate);
            }]);
        }])->get();
    }
}
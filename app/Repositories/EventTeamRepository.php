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

namespace App\Repositories;

use App\Facades\GeneralHelper;
use App\Models\EventTeam;

/**
 * Class EventTeamRepository
 *
 * @package App\Repositories
 */
class EventTeamRepository extends BaseRepository
{

    /**
     * EventTeamRepository constructor.
     *
     * @param \App\Models\EventTeam $eventTeam
     */
    public function __construct(EventTeam $eventTeam)
    {

        $this->model = $eventTeam;
    }

    /**
     * Get team by uuid.
     *
     * @param $uuid
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     * @throws \Exception
     */
    public function getTeamByUuid($uuid)
    {
        return $this->model->with(['event'])->where('uuid', GeneralHelper::uuidToBin($uuid))->first();
    }
}
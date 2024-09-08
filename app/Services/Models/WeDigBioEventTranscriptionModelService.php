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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Models\WeDigBioEventTranscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeDigBioEventTranscriptionModelService
{
    /**
     * WeDigBioEventTranscriptionRepository constructor.
     */
    public function __construct(private readonly WeDigBioEventTranscription $model) {}

    /**
     * Create.
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Get transcriptions for WeDigBio project event step chart.
     */
    public function getWeDigBioRateChartTranscriptions(int $dateId, Carbon $startLoad, Carbon $endLoad): ?Collection
    {
        return $this->model->with(['project:id,title'])
            ->selectRaw('project_id, ADDTIME(FROM_UNIXTIME(FLOOR((UNIX_TIMESTAMP(created_at))/300)*300), "0:05:00") AS time, count(id) as count')
            ->where('date_id', $dateId)
            ->where('created_at', '>=', $startLoad->toDateTimeString())
            ->where('created_at', '<', $endLoad->toDateTimeString())
            ->groupBy('time', 'project_id')
            ->orderBy('time')
            ->get();
    }
}

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

use App\Models\EventTranscription;
use Illuminate\Support\Collection;

/**
 * Class EventTranscriptionRepository
 *
 * @package App\Repositories
 */
class EventTranscriptionRepository extends BaseRepository
{
    /**
     * EventTranscriptionRepository constructor.
     *
     * @param \App\Models\EventTranscription $eventTranscription
     */
    public function __construct(EventTranscription $eventTranscription)
    {

        $this->model = $eventTranscription;
    }

    /**
     * Get event classification ids.
     *
     * @param $eventId
     * @return mixed
     */
    public function getEventClassificationIds($eventId)
    {
        return $this->model->where('event_id', $eventId)->pluck('classification_id');
    }

    /**
     * Get transcriptions for event step chart.
     *
     * @param string $eventId
     * @param string $startLoad
     * @param string $endLoad
     * @return \Illuminate\Support\Collection|null
     */
    public function getEventStepChartTranscriptions(string $eventId, string $startLoad, string $endLoad): ?Collection
    {
        return $this->model->with(['team:id,title'])
            ->selectRaw('event_id, ADDTIME(FROM_UNIXTIME(FLOOR((UNIX_TIMESTAMP(created_at))/300)*300), "0:05:00") AS time, team_id, count(id) as count')
            ->where('event_id', $eventId)
            ->where('created_at', '>=', $startLoad)
            ->where('created_at', '<', $endLoad)
            ->groupBy('time', 'team_id', 'event_id')->orderBy('time')->get();
    }
}
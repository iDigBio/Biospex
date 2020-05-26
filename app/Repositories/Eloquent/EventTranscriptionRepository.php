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

use App\Models\EventTranscription as Model;
use App\Repositories\Interfaces\EventTranscription;
use Illuminate\Support\Collection;

class EventTranscriptionRepository extends EloquentRepository implements EventTranscription
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
    public function getEventClassificationIds($eventId)
    {
        $ids = $this->model->where('event_id', $eventId)->pluck('classification_id');

        $this->resetModel();

        return $ids;
    }

    /**
     * @inheritDoc
     */
    public function getEventStepChartTranscriptions(string $eventId, string $startLoad, string $endLoad): ?Collection
    {
        $result = $this->model->with(['team:id,title'])
            ->selectRaw('event_id, ADDTIME(FROM_UNIXTIME(FLOOR((UNIX_TIMESTAMP(created_at))/300)*300), "0:05:00") AS time, team_id, count(id) as count')
            ->where('event_id', $eventId)
            ->where('created_at', '>=', $startLoad)
            ->where('created_at', '<', $endLoad)
            ->groupBy('time', 'team_id', 'event_id')->orderBy('time')->get();

        $this->resetModel();

        return $result;
    }
}
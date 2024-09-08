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

use App\Models\WeDigBioEventDate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class WeDigBioEventDateRepository
 */
class WeDigBioEventDateRepository extends BaseRepository
{
    /**
     * WeDigBioEventDateRepository constructor.
     */
    public function __construct(WeDigBioEventDate $weDigBioEventDate)
    {
        $this->model = $weDigBioEventDate;
    }

    /**
     * @return mixed|null
     */
    public function getWeDigBioEventTranscriptions(int $dateId): mixed
    {
        $activeEvent = $this->getByActiveOrDateId($dateId);

        if ($activeEvent === null) {
            return null;
        }

        return Cache::remember('wedigbio-event-transcription'.$activeEvent->id, 86400, function () use ($activeEvent) {
            return $this->model->withCount('transcriptions')->with([
                'transcriptions' => function ($q) {
                    $q->select('*', DB::raw('count(project_id) as total'))
                        ->with(['project' => function ($query) {
                            $query->select('id', 'title');
                        }])->groupBy('project_id')
                        ->orderBy('total', 'desc');
                },
            ])->find($activeEvent->id);
        });
    }

    /**
     * Return project titles for WeDigBio Rate chart.
     *
     * @return null
     */
    public function getProjectsForWeDigBioRateChart(?int $dateId = null)
    {
        $activeEvent = $this->getByActiveOrDateId($dateId);

        if ($activeEvent === null) {
            return null;
        }

        $result = $this->model->with(['transcriptions' => function ($q) {
            $q->with(['project' => function ($q2) {
                $q2->select('id', 'title');
            }])->groupBy('project_id');
        }])->find($activeEvent->id);

        return $result->transcriptions->map(function ($transcription) {
            return $transcription->project->title;
        })->toArray();
    }

    /**
     * Get WeDigBioDate by date or active.
     */
    public function getByActiveOrDateId(int $dateId): mixed
    {
        return $dateId === 0 ? $this->model->active()->first() : $this->model->find($dateId);
    }
}

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

namespace App\Services\WeDigBio;

use App\Models\WeDigBioEventDate;
use App\Models\WeDigBioEventTranscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WeDigBioService
{
    /**
     * WeDigBioEventDateRepository constructor.
     */
    public function __construct(
        public WeDigBioEventDate $weDigBioEventDate,
        public WeDigBioEventTranscription $weDigBioEventTranscription
    ) {}

    /**
     * Get results for WeDigBio page.
     */
    public function getWeDigBioPage(): Collection
    {
        return $this->weDigBioEventDate->all()->sortBy('created_at');
    }

    /**
     * @return mixed|null
     */
    public function getWeDigBioEventTranscriptions(?WeDigBioEventDate $event = null): mixed
    {
        $activeEvent = $this->getActiveOrComplete($event);

        if ($activeEvent === null) {
            return null;
        }

        return Cache::remember('wedigbio-event-transcription'.$activeEvent->id, 86400, function () use ($activeEvent) {
            return $this->weDigBioEventDate->withCount('transcriptions')->with([
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
     */
    public function getProjectsForWeDigBioRateChart(?WeDigBioEventDate $event = null): ?array
    {
        $activeEvent = $this->getActiveOrComplete($event);

        if ($activeEvent === null) {
            return null;
        }

        $result = $this->weDigBioEventDate->with(['transcriptions' => function ($q) {
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
    public function getActiveOrComplete(?WeDigBioEventDate $event = null): mixed
    {
        return is_null($event) ? $this->weDigBioEventDate->active()->first() : $this->weDigBioEventDate->find($event->id);
    }

    /**
     * Get transcriptions for WeDigBio project event step chart.
     */
    public function getWeDigBioRateChartTranscriptions(int $dateId, Carbon $startLoad, Carbon $endLoad): ?Collection
    {
        return $this->weDigBioEventTranscription->with(['project:id,title'])
            ->selectRaw('project_id, ADDTIME(FROM_UNIXTIME(FLOOR((UNIX_TIMESTAMP(created_at))/300)*300), "0:05:00") AS time, count(id) as count')
            ->where('date_id', $dateId)
            ->where('created_at', '>=', $startLoad->toDateTimeString())
            ->where('created_at', '<', $endLoad->toDateTimeString())
            ->groupBy('time', 'project_id')
            ->orderBy('time')
            ->get();
    }
}

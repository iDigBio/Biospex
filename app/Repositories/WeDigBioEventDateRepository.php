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
 *
 * @package App\Repositories
 */
class WeDigBioEventDateRepository extends BaseRepository
{
    /**
     * WeDigBioEventDateRepository constructor.
     *
     * @param \App\Models\WeDigBioEventDate $weDigBioEventDate
     */
    public function __construct(WeDigBioEventDate $weDigBioEventDate)
    {
        $this->model = $weDigBioEventDate;
    }

    /**
     * Get transcriptions using date or active.
     *
     * @param int|null $dateId
     * @return mixed
     */
    public function getWeDigBioEventTranscriptions(int $dateId = null): mixed
    {
        return Cache::remember('wedigbio-event-transcription', 86400, function () use($dateId) {
            return $this->model->withCount('transcriptions')->with([
                'transcriptions' => function ($q) use ($dateId) {
                    $q->select('*', DB::raw('count(project_id) as total'))
                        ->with(['project' => function($query){
                            $query->select('id', 'title');
                        }])->groupBy('project_id')
                        ->orderBy('total', 'desc');
                },
            ])->dateOrActive($dateId);
        });
    }

    /**
     * Return project titles for WeDigBio Rate chart.
     *
     * @param int|null $dateId
     * @return array
     */
    public function getProjectsForWeDigBioRateChart(int $dateId = null): array
    {
        $result = $this->model->with(['transcriptions' => function($q){
            $q->with(['project' => function($q2){
                $q2->select('id', 'title');
            }])->groupBy('project_id');
        }])->dateOrActive($dateId);

        return $result->transcriptions->map(function($transcription) {
            return $transcription->project->title;
        })->toArray();
    }

    /**
     * Get WeDigBioDate by date or active.
     *
     * @param int $dateId
     * @return mixed
     */
    public function getByActiveOrDateId(int $dateId): mixed
    {
        $date = empty($date) ? null : $dateId;

        return $this->model->dateOrActive($date);
    }
}
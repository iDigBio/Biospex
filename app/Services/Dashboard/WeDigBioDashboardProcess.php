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

namespace App\Services\Dashboard;

use App\Services\Transcriptions\PusherTranscriptionService;

/**
 * Class WeDigBioDashboardProcess
 */
class WeDigBioDashboardProcess
{
    /**
     * WeDigBioDashboardProcess constructor.
     */
    public function __construct(protected PusherTranscriptionService $pusherTranscriptionService) {}

    /**
     * Set dashboard query
     */
    public function setDashboardQuery(array $request)
    {
        $this->pusherTranscriptionService->setQueryForDashboard($request);
    }

    /**
     * Get dashboard count
     *
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->pusherTranscriptionService->getWeDigBioDashboardCount();
    }

    /**
     * Get dashboard items.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getItems(int $limit, int $offset)
    {
        return $this->pusherTranscriptionService->getWeDigBioDashboardItems($limit, $offset);
    }

    /**
     * Set limit on rows returned.
     */
    public function setLimit(array $request): int
    {
        return (isset($request['rows']) && ((int) $request['rows'] <= 500)) ? (int) $request['rows'] : 500;
    }

    /**
     * Set current page.
     *
     * @return int
     */
    public function setOffset(array $request)
    {
        return isset($request['rowStart']) ? (int) $request['rowStart'] : 0;
    }

    /**
     * Show single resource.
     *
     * @return mixed
     */
    public function showApiDashboard($guid)
    {
        return $this->pusherTranscriptionService->findBy('guid', $guid);
    }
}

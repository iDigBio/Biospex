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

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface ExportQueue extends RepositoryInterface
{
    /**
     * Get queued record or first.
     *
     * @param array $attributes
     * @return mixed
     */
    public function getFirstExportWithoutError(array $attributes = ['*']);

    /**
     * Get first ExportQueue Expedition and Actor with pivot table.
     *
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @return mixed
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId);

    /**
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @param array $attributes
     * @return mixed
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*']);

    /**
     * @return mixed
     */
    public function getAllExportQueueOrderByIdAsc();

}

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
use Illuminate\Support\Collection;

interface ExportQueueFile extends RepositoryInterface
{
    /**
     * Get files from export_queue_files by queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesByQueueId(string $queueId): Collection;

    /**
     * Get files for queue where no error listed.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithoutErrorByQueueId(string $queueId): Collection;

    /**
     * Get queue files with errors using queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithErrorsByQueueId(string $queueId): Collection;
}
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

namespace App\Services\Actor;

use App\Services\Model\DownloadService;
use App\Services\Model\ExpeditionService;
use App\Services\Model\ExportQueueFileService;
use App\Services\Model\ExportQueueService;
use App\Services\Model\SubjectService;

class ZooniverseDbService
{
    /**
     * @var \App\Services\Model\ExportQueueService
     */
    public $exportQueueService;

    /**
     * @var \App\Services\Model\ExportQueueFileService
     */
    public $exportQueueFileService;

    /**
     * @var \App\Services\Model\SubjectService
     */
    public $subjectService;

    /**
     * @var \App\Services\Model\DownloadService
     */
    public $downloadService;

    /**
     * @var \App\Services\Model\ExpeditionService
     */
    public $expeditionService;

    /**
     * ZooniverseDbService constructor.
     *
     * @param \App\Services\Model\ExportQueueService $exportQueueService
     * @param \App\Services\Model\ExportQueueFileService $exportQueueFileService
     * @param \App\Services\Model\SubjectService $subjectService
     * @param \App\Services\Model\DownloadService $downloadService
     * @param \App\Services\Model\ExpeditionService $expeditionService
     */
    public function __construct(
        ExportQueueService $exportQueueService,
        ExportQueueFileService $exportQueueFileService,
        SubjectService $subjectService,
        DownloadService $downloadService,
        ExpeditionService $expeditionService
    )
    {

        $this->exportQueueService = $exportQueueService;
        $this->exportQueueFileService = $exportQueueFileService;
        $this->subjectService = $subjectService;
        $this->downloadService = $downloadService;
        $this->expeditionService = $expeditionService;
    }

    /**
     * Update rejected files.
     *
     * @param array $rejected
     */
    public function updateRejected(array $rejected = [])
    {
        if (empty($rejected)) {
            return;
        }

        foreach ($rejected as $subjectId => $reason) {
            $file = $this->exportQueueFileService->findBy('subject_id', $subjectId);
            if (empty($file)) {
                \Log::info('empty file ' . $subjectId);
                continue;
            }
            $file->error = 1;
            $file->error_message = $reason;
            $file->save();
        }
    }
}
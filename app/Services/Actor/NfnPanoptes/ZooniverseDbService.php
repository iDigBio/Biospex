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

namespace App\Services\Actor\NfnPanoptes;

use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Repositories\SubjectRepository;

class ZooniverseDbService
{
    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    public ExportQueueRepository $exportQueueRepo;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    public ExportQueueFileRepository $exportQueueFileRepo;

    /**
     * @var \App\Repositories\SubjectRepository
     */
    public SubjectRepository $subjectRepo;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    public DownloadRepository $downloadRepo;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    public ExpeditionRepository $expeditionRepo;

    /**
     * ZooniverseDbService constructor.
     * TODO: This class is used to bring many dependencies together. Try looking for better solution.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepo
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepo
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Repositories\DownloadRepository $downloadRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepo,
        ExportQueueFileRepository $exportQueueFileRepo,
        SubjectRepository $subjectRepo,
        DownloadRepository $downloadRepo,
        ExpeditionRepository $expeditionRepo
    )
    {

        $this->exportQueueRepo = $exportQueueRepo;
        $this->exportQueueFileRepo = $exportQueueFileRepo;
        $this->subjectRepo = $subjectRepo;
        $this->downloadRepo = $downloadRepo;
        $this->expeditionRepo = $expeditionRepo;
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
            $file = $this->exportQueueFileRepo->findBy('subject_id', $subjectId);
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
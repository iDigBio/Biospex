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

use App\Jobs\ZooniverseClassificationCountJob;
use App\Jobs\ZooniverseExportBuildCsvJob;
use App\Jobs\ZooniverseExportBuildQueueJob;
use App\Jobs\ZooniverseExportBuildTarJob;
use App\Jobs\ZooniverseExportConvertImageJob;
use App\Jobs\ZooniverseExportDeleteFilesJob;
use App\Jobs\ZooniverseExportReportJob;
use App\Jobs\ZooniverseExportRetrieveImageJob;
use App\Models\Actor;
use App\Notifications\NfnExportError;
use App\Services\Model\ExpeditionService;
use Illuminate\Bus\Batch;
use Notification;

/**
 * Class NfnPanoptes
 *
 * @package App\Services\Actor
 */
class NfnPanoptes
{
    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * NfnPanoptes constructor.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     */
    public function __construct(
        ExpeditionService $expeditionService
    ) {
        $this->expeditionService = $expeditionService;
    }

    /**
     * Process export job.
     *
     * @param \App\Models\Actor $actor
     * @throws \Throwable
     */
    public function actor(Actor $actor)
    {
        $this->actor = $actor;

        if ($actor->pivot->state === 0) {
            \Bus::batch([
                new ZooniverseExportBuildQueueJob($actor),
                new ZooniverseExportRetrieveImageJob($actor),
                //new ZooniverseExportConvertImageJob($actor),
                //new ZooniverseExportBuildCsvJob($actor),
                //new ZooniverseExportBuildTarJob($actor),
                //new ZooniverseExportReportJob($actor),
                //new ZooniverseExportDeleteFilesJob($actor)
            ])->catch(function (Batch $batch, \Exception $exception) {
                $this->sendErrorNotification($exception);
            })->name('Zooniverse Export '.$actor->pivot->expedition_id)->onQueue(config('config.export_tube'))->dispatch();
        } elseif ($actor->pivot->state === 1) {
            ZooniverseClassificationCountJob::dispatch($actor->pivot->expedition_id, $actor);
        }
    }

    /**
     * Send error notification.
     *
     * @param \Exception $exception
     */
    public function sendErrorNotification(\Exception $exception)
    {
        $expedition = $this->expeditionService->findNotifyExpeditionUsers($this->actor->pivot->expedition_id);
        $users = $expedition->project->group->users->push($expedition->project->group->owner);

        $message = [
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        ];

        Notification::send($users, new NfnExportError($expedition->title, $expedition->id, $message));
    }
}
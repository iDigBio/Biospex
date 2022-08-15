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

use App\Jobs\ZooniverseCsvJob;
use App\Jobs\ZooniverseExportBuildCsvJob;
use App\Jobs\ZooniverseExportBuildQueueJob;
use App\Jobs\ZooniverseExportBuildZipJob;
use App\Jobs\ZooniverseExportCheckImageProcessJob;
use App\Jobs\ZooniverseExportDeleteFilesJob;
use App\Jobs\ZooniverseExportCreateReportJob;
use App\Jobs\ZooniverseExportProcessImageJob;
use App\Models\Actor;
use App\Models\Expedition;
use App\Notifications\NfnExportError;
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
     * Process export job.
     *
     * @param \App\Models\Actor $actor
     * @throws \Throwable
     */
    public function actor(Actor $actor)
    {
        if ($actor->pivot->state === 0) {
            \Bus::batch([
                new ZooniverseExportBuildQueueJob($actor),
                new ZooniverseExportProcessImageJob($actor),
                new ZooniverseExportCheckImageProcessJob($actor),
                new ZooniverseExportBuildCsvJob($actor),
                new ZooniverseExportBuildZipJob($actor),
                new ZooniverseExportCreateReportJob($actor),
                new ZooniverseExportDeleteFilesJob($actor)
            ])->catch(function (Batch $batch, \Throwable $exception) use ($actor) {
                $message = [
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getMessage()
                ];
                $expedition = Expedition::with(['project.group' => function($q) {
                    $q->with(['owner', 'users' => function($q){
                        $q->where('notification', 1);
                    }]);
                }])->find($actor->pivot->expedition_id);

                $users = $expedition->project->group->users->push($expedition->project->group->owner);

                Notification::send($users, new NfnExportError($expedition->title, $expedition->id, $message));

            })->name('NfnPanoptes Export '.$actor->pivot->expedition_id)->onQueue(config('config.export_tube'))->dispatch();
        } elseif ($actor->pivot->state === 1) {
            ZooniverseCsvJob::dispatch($actor->pivot->expedition_id);
        }
    }
}
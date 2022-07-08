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

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Notifications\NfnExportComplete;
use App\Services\Actor\ActorInterface;
use Notification;

/**
 * Class ZooniverseExportReport
 *
 * @package App\Services\Actor
 */
class ZooniverseExportReport extends ZooniverseBase implements ActorInterface
{
    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueRepo->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 4;
        $queue->save();

        \Artisan::call('export:poll');

        $files = $this->dbService->exportQueueFileRepo->getFilesByQueueId($queue->id, 1);

        try {
            $remove = array_flip(['id', 'queue_id', 'error', 'created_at', 'updated_at']);
            $data = $files->map(function ($file) use ($remove) {
                return array_diff_key($file->toArray(), $remove);
            });

            $csvName = md5($queue->id).'.csv';
            $fileName = $this->csv->createReportCsv($data->toArray(), $csvName);

            if(isset($fileName)) {
                $this->saveReport($queue, $csvName);
            }

            $expedition = $this->dbService->expeditionRepo->findNotifyExpeditionUsers($queue->expedition_id);
            $users = $expedition->project->group->users->push($expedition->project->group->owner);

            Notification::send($users, new NfnExportComplete($expedition->title, $fileName));

        } catch (\Exception $exception) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * Save report.
     *
     * @param \App\Models\ExportQueue $queue
     * @param string $csvName
     */
    private function saveReport(ExportQueue $queue, string $csvName)
    {
        $attributes = [
            'expedition_id' => $queue->expedition_id,
            'actor_id' => $queue->actor_id,
            'type' => 'report'
        ];
        $values = [
            'expedition_id' => $queue->expedition_id,
            'actor_id' => $queue->actor_id,
            'file' => $csvName,
            'type' => 'report'
        ];

        $this->dbService->downloadRepo->updateOrCreate($attributes, $values);
    }
}
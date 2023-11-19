<?php
/*
 * Copyright (c) 2022. Biospex
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

namespace App\Services\Actors\Zooniverse\Traits;

use App\Models\ExportQueue;
use App\Notifications\Generic;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait ZooniverseErrorNotification
{
    /**
     * Send error notification.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \Throwable $throwable
     * @return void
     */
    private function sendErrorNotification(ExportQueue $exportQueue, Throwable $throwable): void
    {
        $exportQueue->load([
            'expedition.project.group' => function($q) {
                $q->with(['owner', 'users' => function($q){
                    $q->where('notification', 1);
                }]);
            }
        ]);

        $exportQueue->error = 1;
        $exportQueue->queued = 0;
        $exportQueue->processed = 0;
        $exportQueue->save();

        $users = $exportQueue->expedition->project->group->users->push($exportQueue->expedition->project->group->owner);

        $attributes = [
            'subject' => t('Error Exporting For Zooniverse'),
            'html'    => [
                t('An error occurred while exporting.'),
                t('The Biospex Administration has been notified and will investigate the issue. Please do not attempt to restart export or perform other functions on this project.'),
                t('Expedition: %s', $exportQueue->expedition->title),
                t('Expedition Id: %s', $exportQueue->expedition->id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified.')
            ]
        ];

        Notification::send($users, new Generic($attributes, true));
    }
}
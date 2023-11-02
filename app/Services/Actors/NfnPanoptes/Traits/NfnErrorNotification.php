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

namespace App\Services\Actors\NfnPanoptes\Traits;

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Models\User;
use App\Notifications\JobError;
use App\Notifications\NfnExportError;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait NfnErrorNotification
{
    /**
     * Send error notification.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \Throwable $exception
     * @return void
     */
    private function sendErrorNotification(ExportQueue $exportQueue, Throwable $exception)
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

        $message = [
            'Queue Id: ' . $exportQueue->id,
            'Expedition Id: ' . $exportQueue->expedition_id,
            'Expedition Title: ' . $exportQueue->expedition->title,
            'Error:' . $exception->getFile() . ': ' . $exception->getLine() . ' - ' . $exception->getMessage()
        ];

        $users = $exportQueue->expedition->project->group->users->push($exportQueue->expedition->project->group->owner);

        Notification::send($users, new NfnExportError($exportQueue->expedition->title, $exportQueue->expedition->id, $message));

    }

    /**
     * Send email to Admin.
     *
     * @param \Throwable $exception
     * @return void
     */
    private function sendAdminError(Throwable $exception)
    {
        $user = User::find(1);
        $messages = [
            'File: ' . $exception->getFile(),
            'Line: ' . $exception->getLine(),
            'Error:' . $exception->getMessage()
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
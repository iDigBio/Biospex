<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Traits;

use App\Notifications\Generic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait NotifyOnJobFailure
{
    /**
     * Notify group users + owner on job failure.
     *
     * @param  Model  $model  Any model with expedition → project → group
     */
    protected function notifyGroupOnFailure(Model $model, Throwable $throwable): void
    {
        $model->load([
            'expedition.project.group' => function ($q) {
                $q->with([
                    'owner', 'users' => function ($q) {
                        $q->where('notification', 1);
                    },
                ]);
            },
        ]);

        $group = $model->expedition->project->group;
        $users = $group->users->push($group->owner);

        $attributes = [
            'subject' => t('Export Job Failed'), 'html' => [
                t('An error occurred during export processing.'), t('Expedition: %s', $model->expedition->title),
                t('Error: %s', $throwable->getMessage()), t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()), t('Admins have been notified.'),
            ],
        ];

        Notification::send($users, new Generic($attributes, true));
    }
}

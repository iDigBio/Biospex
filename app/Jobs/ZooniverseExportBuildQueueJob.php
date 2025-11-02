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

namespace App\Jobs;

use App\Livewire\ProcessMonitor;
use App\Models\ActorExpedition;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Subject\SubjectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class ZooniverseExportBuildQueueJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 3600;

    public function __construct(protected ActorExpedition $actorExpedition)
    {
        $this->actorExpedition = $actorExpedition->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    public function handle(SubjectService $subjectService): void
    {
        \Log::info("ZooniverseExportBuildQueueJob actor ID: {$this->actorExpedition->actor_id}");

        $this->actorExpedition->load('expedition');

        // === CREATE OR UPDATE EXPORT QUEUE ===
        $queue = ExportQueue::firstOrNew([
            'expedition_id' => $this->actorExpedition->expedition_id,
            'actor_id' => $this->actorExpedition->actor_id,
        ]);

        $queue->queued = 0;
        $queue->error = 0;
        $queue->stage = 0;
        $queue->total = $this->actorExpedition->total;
        $queue->save();

        // === BUILD FILES ===
        $subjects = $subjectService->getSubjectCursorForExport($this->actorExpedition->expedition_id);

        $subjects->each(function ($subject) use ($queue) {
            ExportQueueFile::updateOrCreate(
                [
                    'queue_id' => $queue->id,
                    'subject_id' => (string) $subject->_id,
                ],
                [
                    'access_uri' => $subject->accessURI,
                ]
            );
        });
    }

    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Export Build Queue Job Failed'),
            'html' => [
                t('An error occurred building the export queue.'),
                t('Actor Id: %s', $this->actorExpedition->actor_id),
                t('Expedition Id: %s', $this->actorExpedition->expedition_id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        if ($user) {
            $user->notify(new Generic($attributes));
        }
    }
}

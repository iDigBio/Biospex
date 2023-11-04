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

namespace App\Jobs;

use App\Models\Expedition;
use App\Models\User;
use App\Notifications\JobError;
use App\Notifications\RecordDeleteComplete;
use App\Repositories\SubjectRepository;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteExpeditionJob
 *
 * @package App\Jobs
 */
class DeleteExpeditionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private User $user;

    /**
     * @var \App\Models\Expedition
     */
    private Expedition $expedition;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Expedition $expedition
     */
    public function __construct(User $user, Expedition $expedition)
    {
        $this->user = $user;
        $this->expedition = $expedition;
        $this->onQueue(config('config.queues.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(
        SubjectRepository $subjectRepo,
        MongoDbService $mongoDbService
    ): void {

        $this->expedition->load('downloads');

        $this->expedition->downloads->each(function ($download) {
            Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
        });

        $mongoDbService->setCollection('pusher_transcriptions');
        $mongoDbService->deleteMany(['expedition_uuid' => $this->expedition->uuid]);

        $mongoDbService->setCollection('panoptes_transcriptions');
        $mongoDbService->deleteMany(['subject_expeditionId' => $this->expedition->id]);

        $subjectIds = $subjectRepo->findByExpeditionId((int) $this->expedition->id, ['_id'])->pluck('_id');

        if ($subjectIds->isNotEmpty()) {
            $subjectRepo->detachSubjects($subjectIds, $this->expedition->id);
        }

        $this->expedition->delete();

        $message = [
            t('Expedition `%s` and all corresponding records have been deleted.', $this->expedition->title),
        ];

        $this->user->notify(new RecordDeleteComplete($message));
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $messages = [
            'Error: '.t('Could not delete Expedition %s', $this->expedition->title),
            t('Error: %s', $throwable->getMessage()),
            t('File: %s', $throwable->getFile()),
            t('Line: %s', $throwable->getLine()),
        ];

        $this->user->notify(new JobError(__FILE__, $messages));
    }
}

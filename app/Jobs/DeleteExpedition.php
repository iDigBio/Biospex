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
use App\Repositories\ExpeditionRepository;
use App\Repositories\SubjectRepository;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class DeleteExpedition
 *
 * @package App\Jobs
 */
class DeleteExpedition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var \App\Models\Expedition
     */
    private $expedition;

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
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        SubjectRepository $subjectRepo,
        MongoDbService $mongoDbService
    ) {

        $expedition = $expeditionRepo->findWith($this->expedition->id, ['downloads']);

        try {

            $expedition->downloads->each(function ($download) {
                Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
            });

            $mongoDbService->setCollection('pusher_transcriptions');
            $mongoDbService->deleteMany(['expedition_uuid' => $expedition->uuid]);

            $mongoDbService->setCollection('panoptes_transcriptions');
            $mongoDbService->deleteMany(['subject_expeditionId' => $expedition->id]);

            $subjectIds = $subjectRepo->findByExpeditionId((int) $this->expedition->id, ['_id'])->pluck('_id');

            if ($subjectIds->isNotEmpty()) {
                $subjectRepo->detachSubjects($subjectIds, $expedition->id);
            }

            $expedition->delete();

            $message = [
                t('Expedition `%s` and all corresponding records have been deleted.', $expedition->title),
            ];
            $this->user->notify(new RecordDeleteComplete($message));
        } catch (\Exception $e) {
            $message = [
                'Error: '.t('Could not delete Expedition %s', $expedition->title),
                'Message:'.$e->getFile().': '.$e->getLine().' - '.$e->getMessage().' - '. $e->getTraceAsString(),
            ];
            $this->user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}

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

namespace App\Services\Reconcile;

use App\Jobs\ExpertReviewMigrateReconcilesJob;
use App\Jobs\ExpertReviewSetProblemsJob;
use App\Jobs\ZooniverseClassificationCountJob;
use App\Jobs\ZooniversePusherJob;
use App\Jobs\ZooniverseTranscriptionJob;
use App\Repositories\DownloadRepository;
use App\Services\Api\AwsLambdaApiService;
use App\Traits\SkipZooniverse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

/**
 * Class ReconcileService
 *
 * Handles all methods for the reconcile and explained process.
 *
 * @see \App\Listeners\LabelReconciliationListener
 */
class ReconcileService
{
    use SkipZooniverse;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepo;

    /**
     * @var \App\Services\Api\AwsLambdaApiService
     */
    private AwsLambdaApiService $awsLambdaApiService;

    /**
     * ReconcileService constructor.
     *
     * @param \App\Repositories\DownloadRepository $downloadRepo
     * @param \App\Services\Api\AwsLambdaApiService $awsLambdaApiService
     */
    public function __construct(DownloadRepository $downloadRepo, AwsLambdaApiService $awsLambdaApiService)
    {
        $this->downloadRepo = $downloadRepo;
        $this->awsLambdaApiService = $awsLambdaApiService;
    }

    /**
     * Process event from Listener.
     *
     * @param int $expeditionId
     * @param bool $explanations
     * @throws \Throwable
     */
    public function processEvent(int $expeditionId, bool $explanations): void
    {
        if ($explanations) {
            $this->processExplained($expeditionId);

            return;
        }

        $this->processReconcile($expeditionId);
    }

    /**
     * After lambda creation of explained file, process expedition:
     * @see ExpertReviewMigrateReconcilesJob
     * @see ExpertReviewSetProblemsJob
     *
     * @param int $expeditionId
     * @throws \Throwable
     */
    public function processExplained(int $expeditionId): void
    {
        Bus::batch([
            new ExpertReviewMigrateReconcilesJob($expeditionId),
            new ExpertReviewSetProblemsJob($expeditionId)
        ])->name('Expert Reconcile ' . $expeditionId)->onQueue(config('config.queues.reconcile'))->dispatch();
    }

    /**
     * Process explained file via lambda labelReconciliations.
     * @see \App\Http\Controllers\Admin\ReconcileController::create
     * @see \App\Console\Commands\ZooniverseExpertReviewCommand::handle
     *
     * @param int $expeditionId
     */
    public function invokeLambdaExplained(int $expeditionId): void
    {
        $attributes = [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'key' => config('zooniverse.directory.classification').'/'.$expeditionId.'.csv',
            'explanations' => true
        ];

        $this->awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_reconciliation_function'), $attributes);
    }

    /**
     * Process returned reconcile event. Pass on to:
     * @see ZooniverseTranscriptionJob
     * @see ZooniversePusherJob
     * @see ZooniverseClassificationCountJob
     *
     * @param int $expeditionId
     */
    public function processReconcile(int $expeditionId): void
    {
        $this->updateOrCreateDownloads($expeditionId);

        ZooniverseTranscriptionJob::withChain([
            new ZooniversePusherJob($expeditionId),
            new ZooniverseClassificationCountJob($expeditionId)
        ])->dispatch($expeditionId);
    }

    /**
     * Update or create downloads for reconcile files produced.
     *
     * @param $expeditionId
     */
    protected function updateOrCreateDownloads($expeditionId): void
    {
        collect(config('zooniverse.file_types'))->each(function ($type) use ($expeditionId) {
            $values = [
                'expedition_id' => $expeditionId,
                'actor_id'      => config('zooniverse.actor_id'),
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
                'updated_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $attributes = [
                'expedition_id' => $expeditionId,
                'actor_id'      => config('zooniverse.actor_id'),
                'file'          => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type'          => $type,
            ];

            $this->downloadRepo->updateOrCreate($attributes, $values);
        });
    }

}
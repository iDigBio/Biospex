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

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseProcessCsvJob
 *
 * @package App\Jobs
 */
class ZooniverseProcessCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 4;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Exception
     */
    public function handle(ZooniverseCsvService $service)
    {
        $result = $service->checkCsvRequest($this->expeditionId);
        if ($result['media'][0]['metadata']['state'] === 'creating') {
            if ($this->attempts() > 3) {
                throw new \Exception(t('NfnPanoptes csv creation for Expedition Id %s exceeded number of tries.', $this->expeditionId));
            }

            $this->release(1800);
        }

        $uri = $result['media'][0]['src'] ?? null;
        if ($uri === null) {
            throw new \Exception(t('NfnPanoptes csv uri for Expedition Id %s is missing', $this->expeditionId));
        }

        ZooniverseCsvDownloadJob::withChain([
            new ZooniverseReconcileJob($this->expeditionId),
            new ZooniverseTranscriptionJob($this->expeditionId),
            new ZooniversePusherJob($this->expeditionId),
            new ZooniverseClassificationCountJob($this->expeditionId),
        ])->dispatch($this->expeditionId, $uri);
    }

    /**
     * Prevent job overlap using expeditionId.
     *
     * @return \Illuminate\Queue\Middleware\WithoutOverlapping[]
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->expeditionId)];
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $user = User::find(1);
        $messages = [
            t('Error: %s', $exception->getMessage()),
            t('File: %s', $exception->getFile()),
            t('Line: %s', $exception->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}

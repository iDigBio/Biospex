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

use App\Jobs\Traits\SkipZooniverse;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

/**
 * Class ZooniverseCsvJob
 *
 * @package App\Jobs
 */
class ZooniverseCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SkipZooniverse;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var bool
     */
    private bool $noDelay;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param bool $noDelay
     */
    public function __construct(int $expeditionId, bool $noDelay = false)
    {
        $this->onQueue(config('config.queue.classification'));
        $this->expeditionId = $expeditionId;
        $this->noDelay = $noDelay;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Exception
     */
    public function handle(ZooniverseCsvService $service)
    {
        if ($this->skipApi($this->expeditionId)) {
            $this->delete();
            return;
        }

        $result = $service->checkCsvRequest($this->expeditionId);

        if (!$result || $service->checkDateTime($result)) {
            $service->createCsvRequest($this->expeditionId);
        }

        if ($this->noDelay) {
            $this->delete();
            return;
        }

        ZooniverseProcessCsvJob::dispatch($this->expeditionId)->delay(now()->addMinutes(30));
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
     * @param  \Throwable  $exception
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
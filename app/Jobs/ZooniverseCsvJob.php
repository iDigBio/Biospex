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
use App\Notifications\Generic;
use App\Services\Csv\ZooniverseCsvService;
use App\Traits\SkipZooniverse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

/**
 * Class ZooniverseCsvJob
 */
class ZooniverseCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SkipZooniverse;

    /**
     * Create a new job instance.
     * noDelay is used to skip the delay in the job when using commands.
     */
    public function __construct(protected int $expeditionId, protected bool $noDelay = false)
    {
        $this->onQueue(config('config.queue.classification'));
    }

    /**
     * Execute the job.
     *
     * @return void
     *
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

        if (! $result || $service->checkDateTime($result)) {
            $service->createCsvRequest($this->expeditionId);
        }

        if ($this->noDelay) {
            $this->delete();

            return;
        }

        ZooniverseProcessCsvJob::dispatch($this->expeditionId)->delay(now()->addHours(6));
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
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $attributes = [
            'subject' => t('Zooniverse CSV Job Failed'),
            'html' => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}

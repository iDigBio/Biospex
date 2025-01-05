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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseProcessCsvJob
 */
class ZooniverseProcessCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 4;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $expeditionId)
    {
        $this->onQueue(config('config.queue.classification'));
    }

    /**
     * Execute the job.
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Exception
     */
    public function handle(ZooniverseCsvService $service): void
    {
        $result = $service->checkCsvRequest($this->expeditionId);
        if ($result['media'][0]['metadata']['state'] === 'creating') {
            if ($this->attempts() > 3) {
                throw new \Exception(t('Zooniverse csv creation for Expedition Id %s exceeded number of tries.', $this->expeditionId));
            }

            $this->release(7200);

            return;
        }

        $uri = $result['media'][0]['src'] ?? null;
        if ($uri === null) {
            throw new \Exception(t('Zooniverse csv uri for Expedition Id %s is missing', $this->expeditionId));
        }

        ZooniverseCsvDownloadJob::dispatch($this->expeditionId, $uri);
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
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Process CSV Failed'),
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

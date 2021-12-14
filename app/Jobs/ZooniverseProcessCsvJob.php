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
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ZooniverseProcessCsvJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var int
     */
    private int $tries;

    /**
     * @param int $expeditionId
     * @param int $tries
     */
    public function __construct(int $expeditionId, int $tries = 0)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expeditionId = $expeditionId;
        $this->tries = $tries;
    }

    /**
     * Execute Job.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(ZooniverseCsvService $service)
    {
        try {
            $result = $service->checkCsvRequest($this->expeditionId);

            if ($result['media'][0]['metadata']['state'] === 'creating') {
                if ($this->tries === 3) {
                    throw new \Exception(t('Zooniverse csv creation for Expedition Id %s exceeded number of tries.', $this->expeditionId));
                }

                $this->tries++;

                $this->dispatch($this->expeditionId, $this->tries)->delay(now()->addMinutes(30));

                $this->delete();
            }

            $uri = $result['media'][0]['src'] ?? null;
            if ($uri === null) {
                throw new \Exception(t('Zooniverse csv uri for Expedition Id %s is missing', $this->expeditionId));
            }

            ZooniverseCsvDownloadJob::withChain([
                new ZooniverseReconcileJob($this->expeditionId),
                new ZooniverseTranscriptionJob($this->expeditionId),
                new ZooniversePusherJob($this->expeditionId),
            ])->dispatch($this->expeditionId, $uri);

            $this->delete();
        } catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));
        }
    }
}
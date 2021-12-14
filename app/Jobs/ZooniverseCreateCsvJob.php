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

/**
 * Class ZooniverseCreateCsvJob
 *
 * @package App\Jobs
 */
class ZooniverseCreateCsvJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.classification_tube'));
        $this->expeditionId = $expeditionId;
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
            $service->createCsvRequest($this->expeditionId);
            ZooniverseProcessCsvJob::dispatch($this->expeditionId)->delay(now()->addMinutes(30));

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
<?php
/**
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

use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Api\PanoptesApiService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationCsvDownloadJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var array
     */
    private $sources;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * NfnClassificationCsvDownloadJob constructor.
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param User $userContract
     * @return void
     */
    public function handle(
        PanoptesApiService $panoptesApiService,
        User $userContract
    )
    {
        if (count($this->sources) === 0)
        {
            return;
        }

        try
        {
            $responses = $panoptesApiService->panoptesClassificationsDownload($this->sources);
            $expeditionIds = [];
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = 'Expedition Id: ' . $index . '<br />' . $response->getMessage();
                    continue;
                }

                $expeditionIds[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            foreach ($expeditionIds as $expeditionId) {
                NfnClassificationReconciliationJob::dispatch((int) $expeditionId)->delay(1800);
            }
        }
        catch (Exception $e)
        {
            return;
        }
    }
}

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

use App\Jobs\Traits\SkipNfn;
use App\Repositories\Interfaces\Expedition;
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

class NfnClassificationCsvCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * Expedition expeditionIds pass to the job.
     *
     * @var null
     */
    private $expeditionIds;

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @var bool
     */
    private $command;

    /**
     * Create a new job instance.
     *
     * NfNClassificationsCsvJob constructor.
     *
     * @param array $expeditionIds
     * @param bool $command
     */
    public function __construct(array $expeditionIds = [], $command = false)
    {
        $this->expeditionIds = $expeditionIds;
        $this->command = $command;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle the job.
     *
     * @param Expedition $expeditionContract
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param User $userContract
     */
    public function handle(
        Expedition $expeditionContract,
        PanoptesApiService $panoptesApiService,
        User $userContract
    )
    {
        foreach ($this->expeditionIds as $key => $expeditionId) {
            if ($this->skipApi($expeditionId)) {
                unset($this->expeditionIds[$key]);
            }
        }

        try
        {
            $expeditions = $expeditionContract->getExpeditionsForNfnClassificationProcess($this->expeditionIds);

            $expeditionIds = [];
            $responses = $panoptesApiService->panoptesClassificationCreate($expeditions);
            foreach ($responses as $index => $response)
            {
                if ($response instanceof ServerException || $response instanceof ClientException)
                {
                    $this->errorMessages[] = $response->getMessage();
                    continue;
                }

                $expeditionIds[] = $index;
            }

            if ( ! empty($this->errorMessages))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $this->errorMessages));
            }

            (empty($expeditionIds) || $this->command) ?
                $this->delete() :
                NfnClassificationCsvFileJob::dispatch($expeditionIds)->delay(14400);
        }
        catch (Exception $e)
        {
            return;
        }
    }
}

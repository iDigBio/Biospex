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
use App\Notifications\EventCsvExport;
use App\Notifications\EventCsvExportError;
use App\Services\Model\EventTranscriptionService;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\Csv\Csv;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Str;

/**
 * Class EventTranscriptionExportCsvJob
 *
 * @package App\Jobs
 */
class EventTranscriptionExportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var User
     */
    private $user;

    /**
     * @var
     */
    private $eventId;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param null $eventId
     */
    public function __construct(User $user, $eventId)
    {
        $this->user = $user;
        $this->eventId = $eventId;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\EventTranscriptionService $eventTranscriptionService
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscriptionContract
     * @param Csv $csv
     * @return void
     */
    public function handle(
        EventTranscriptionService $eventTranscriptionService,
        PanoptesTranscription $panoptesTranscriptionContract,
        Csv $csv
    )
    {
        try
        {
            $ids = $eventTranscriptionService->getEventClassificationIds($this->eventId);

            $transcriptions = $ids->map(function($id) use($panoptesTranscriptionContract) {
                $transcript = $panoptesTranscriptionContract->findBy('classification_id', $id);
                unset($transcript['_id']);
                return $transcript;
            })->reject(function($transcription){
                return $transcription === null;
            });

            $csvFileName = $fileName = Str::random() . '.csv';
            $fileName = $transcriptions->isEmpty() ? null : $csv->createReportCsv($transcriptions->toArray(), $csvFileName);

            $this->user->notify(new EventCsvExport($fileName));
        }
        catch (Exception $e)
        {
            $this->user->notify(new EventCsvExportError($e->getMessage()));
        }
    }
}

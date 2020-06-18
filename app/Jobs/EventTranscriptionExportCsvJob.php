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

use App\Models\User;
use App\Notifications\EventCsvExport;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Services\Csv\Csv;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Str;

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
     * @param \App\Repositories\Interfaces\EventTranscription $eventTranscriptionContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscriptionContract
     * @param Csv $csv
     * @return void
     */
    public function handle(
        EventTranscription $eventTranscriptionContract,
        PanoptesTranscription $panoptesTranscriptionContract,
        Csv $csv
    )
    {
        try
        {
            $ids = $eventTranscriptionContract->getEventClassificationIds($this->eventId);

            $transcriptions = $ids->map(function($id) use($panoptesTranscriptionContract) {
                $transcript = $panoptesTranscriptionContract->findBy('classification_id', $id);
                unset($transcript['_id']);
                return $transcript;
            })->reject(function($transcription){
                return $transcription === null;
            });

            $file = $transcriptions->isEmpty() ? null : $this->setCsv($transcriptions, $csv);

            $this->user->notify(new EventCsvExport(trans('pages.event_export_csv_complete'), $file));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new EventCsvExport(trans('pages.event_export_csv_error', ['error' => $e->getMessage()])));
        }
    }

    /**
     * @param $transcriptions
     * @param \App\Services\Csv\Csv $csv
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    private function setCsv($transcriptions, Csv $csv) {
        $first = $transcriptions->first()->toArray();
        $header = array_keys($first);

        $file = \Storage::path(config('config.reports_dir') . '/' . Str::random() . '.csv');
        $csv->writerCreateFromPath($file);
        $csv->insertOne($header);
        $csv->insertAll($transcriptions->toArray());

        return $file;
    }
}

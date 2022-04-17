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

use App\Facades\TranscriptionMapHelper;
use App\Models\PanoptesTranscription;
use App\Models\PanoptesTranscriptionNew;
use App\Models\User;
use App\Notifications\JobComplete;
use App\Notifications\JobError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Validator;

class EncodeTranscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 300000;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onConnection('long-beanstalkd')->onQueue(config('config.working_tube'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find(1);
        try {
            $cursor = PanoptesTranscription::orderBy('created_at', 'DESC')->cursor();

            $i=0;
            foreach ($cursor as $record) {
                if ($this->validateTranscription($record->classification_id)) {
                    continue;
                }

                $newRecord = [];
                foreach ($record->getAttributes() as $field => $value) {
                    $newField = TranscriptionMapHelper::encodeTranscriptionField($field);
                    $newRecord[$newField] = $value;
                }

                PanoptesTranscriptionNew::create($newRecord);
                $i++;
            }

            $message = [
                'Transcript sync completed',
                'Transcripts synced: ' . $i
            ];
            $user->notify(new JobComplete(__FILE__, $message));

            $this->delete();

        } catch (\Exception $e) {
            $message = [
                'Error in Transcript syncing',
                'Message: ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param int $classificationId
     * @return mixed
     */
    private function validateTranscription(int $classificationId): mixed
    {
        $rules = ['classification_id' => 'unique:mongodb.panoptes_transcriptions_new,classification_id'];
        $values = ['classification_id' => $classificationId];
        $validator = Validator::make($values, $rules);

        // returns true if record exists.
        return $validator->fails();
    }
}

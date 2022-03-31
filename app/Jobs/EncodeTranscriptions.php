<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Models\PanoptesTranscription;
use App\Models\PanoptesTranscriptionNew;
use App\Models\User;
use App\Notifications\JobError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Validator;

class EncodeTranscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue(config('config.working_tube'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $reserved = config('config.reserved_encoded');
        $user = User::find(1);
        try {
            foreach (PanoptesTranscription::orderBy('created_at', 'DESC')->cursor() as $record) {
                $newRecord = [];
                foreach ($record->getAttributes() as $key => $value) {
                    if ($key === '_id') {
                        continue;
                    }
                    $newKey = (str_contains($key, 'subject_') || in_array($key, $reserved)) ? $key : GeneralHelper::base64UrlEncode($key);
                    $newRecord[$newKey] = $value;
                }

                if (! $this->validateTranscription($newRecord['classification_id'])) {
                    PanoptesTranscriptionNew::create($newRecord);
                }
            }

            $message = [
                'Transcript encoding completed'
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();

        } catch (\Exception $e) {
            $message = [
                'Error in Transcript encoding',
                'Message: ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $id
     * @return mixed
     */
    private function validateTranscription($id): mixed
    {
        $rules = ['classification_id' => 'unique:mongodb.panoptes_transcriptions_new, classification_id'];
        $values = ['classification_id' => (int) $id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if record exists.
        return $validator->fails();
    }
}

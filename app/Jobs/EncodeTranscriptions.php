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
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private mixed $reserved;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reserved = config('config.reserved_encoded');
        $this->onQueue(config('config.working_tube'));
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
            foreach (PanoptesTranscription::orderBy('created_at', 'DESC')->cursor() as $record) {
                $newRecord = [];
                foreach ($record->getAttributes() as $key => $value) {
                    $newKey = (str_contains($key, 'subject_') || in_array($key, $this->reserved)) ? $key : GeneralHelper::base64UrlEncode($key);
                    $newRecord[$newKey] = $value;
                }

                if (! $this->validateTranscription($newRecord['subject_id'])) {
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
     * @param $subject_id
     * @return mixed
     */
    private function validateTranscription($subject_id): mixed
    {
        $rules = ['subject_id' => 'unique:mongodb.panoptes_transcriptions_new, subject_id'];
        $values = ['subject_id' => (int) $subject_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if record exists.
        return $validator->fails();
    }
}
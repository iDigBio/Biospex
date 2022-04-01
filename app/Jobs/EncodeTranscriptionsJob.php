<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
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
                if ($this->validateTranscription($record->classification_id)) {
                    continue;
                }

                $newRecord = [];
                foreach ($record->getAttributes() as $key => $value) {
                    $newKey = (str_contains($key, 'subject_') || in_array($key, $reserved)) ? $key : GeneralHelper::base64UrlEncode($key);
                    $newRecord[$newKey] = $value;
                }

                PanoptesTranscriptionNew::create($newRecord);
            }

            $message = [
                'Transcript encoding completed'
            ];
            $user->notify(new JobComplete(__FILE__, $message));

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

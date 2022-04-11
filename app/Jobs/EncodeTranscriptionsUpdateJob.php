<?php

namespace App\Jobs;

use App\Facades\GeneralHelper;
use App\Models\PanoptesTranscription;
use App\Models\PanoptesTranscriptionNew;
use App\Models\Reconcile;
use App\Models\User;
use App\Notifications\JobComplete;
use App\Notifications\JobError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Validator;

class EncodeTranscriptionsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 300000;

    /**
     * @var array
     */
    private array $reserved;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reserved = config('config.reserved_encoded');
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
            $timestamp = Carbon::now()->subDays(2);
            $cursor = PanoptesTranscription::where('created_at', '>=', $timestamp)
                ->orderBy('created_at', 'DESC')
                ->cursor();

            $i=0;
            foreach ($cursor as $record) {
                if ($this->validateTranscription($record->classification_id)) {
                    continue;
                }

                $newRecord = [];
                foreach ($record->getAttributes() as $field => $value) {
                    $newField = GeneralHelper::encodeCsvFields($field, $this->reserved);
                    $newRecord[$newField] = $value;
                }

                PanoptesTranscriptionNew::create($newRecord);
                $i++;
            }

            $message = [
                'Transcript update completed',
                'Transcripts updated: ' . $i
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

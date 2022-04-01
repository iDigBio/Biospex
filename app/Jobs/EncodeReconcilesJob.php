<?php

namespace App\Jobs;

use App\Models\Reconcile;
use App\Models\ReconcileNew;
use App\Models\User;
use App\Notifications\JobComplete;
use App\Notifications\JobError;
use GeneralHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Validator;

class EncodeReconcilesJob implements ShouldQueue
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
        //
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
            $timestamp = Carbon::now()->subDays(2);
            $cursor = Reconcile::where('created_at', '>=', $timestamp)
                ->orderBy('created_at', 'DESC')
                ->cursor();

            foreach ($cursor as $record) {
                if ($this->validateTranscription($record->subject_id)) {
                    continue;
                }

                $newRecord = [];
                foreach ($record->getAttributes() as $key => $value) {
                    $newKey = (str_contains($key, 'subject_') || in_array($key, $reserved)) ? $key : GeneralHelper::base64UrlEncode($key);
                    $newRecord[$newKey] = $value;
                }

                ReconcileNew::create($newRecord);
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
     * Validate reconcile to prevent duplicates.
     *
     * @param int $subjectId
     * @return mixed
     */
    private function validateTranscription(int $subjectId): mixed
    {
        $rules = ['subject_id' => 'unique:mongodb.reconciles_new,subject_id'];
        $values = ['subject_id' => $subjectId];
        $validator = Validator::make($values, $rules);

        // returns true if record exists.
        return $validator->fails();
    }
}

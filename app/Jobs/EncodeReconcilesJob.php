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
            $cursor = Reconcile::orderBy('created_at', 'DESC')->cursor();

            $i=0;
            foreach ($cursor as $record) {
                if ($this->validateTranscription($record->subject_id)) {
                    continue;
                }

                $newRecord = [];
                foreach ($record->getAttributes() as $field => $value) {
                    $newField = GeneralHelper::encodeCsvFields($field, $this->reserved);
                    $newField = $newField === 'problem' ? 'subject_problem' : $newField;
                    $newField = $newField === 'columns' ? 'subject_columns' : $newField;

                    $newRecord[$newField] = $value;
                }

                if(!isset($newRecord['subject_problem'])) {
                    $newRecord['subject_problem'] = 0;
                }

                if(!isset($newRecord['subject_columns'])) {
                    $newRecord['subject_columns'] = '';
                }

                ReconcileNew::create($newRecord);
                $i++;
            }

            $message = [
                'Reconciles sync completed.',
                'Reconciles synced: ' . $i
            ];
            $user->notify(new JobComplete(__FILE__, $message));

            $this->delete();

        } catch (\Exception $e) {
            $message = [
                'Error in Reconciles sync',
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

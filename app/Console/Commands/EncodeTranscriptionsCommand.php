<?php

namespace App\Console\Commands;

use App\Facades\GeneralHelper;
use App\Models\PanoptesTranscription;
use App\Models\PanoptesTranscriptionNew;
use Illuminate\Console\Command;
use Validator;

class EncodeTranscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encode:transcriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to encode transcription and reconcile columns';

    private array $reserved;

    /**
     * AppCommand constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->reserved = config('config.reserved_encoded');
    }

    /**
     * @throws \Exception
     * _id, updated_at, created_at
     */
    public function handle()
    {
        foreach (PanoptesTranscription::orderBy('created_at', 'DESC')->cursor() as $record) {
            $newRecord = [];
            foreach ($record->getAttributes() as $key => $value) {
                $newKey = (str_contains($key, 'subject_') || in_array($key, $this->reserved)) ? $key : GeneralHelper::base64UrlEncode($key);
                $newRecord[$newKey] = $value;
            }

            if (!$this->validateTranscription($newRecord['subject_id'])) {
                PanoptesTranscriptionNew::create($newRecord);
            }
        }

    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $subject_id
     * @return mixed
     */
    private function validateTranscription($subject_id)
    {
        $rules = ['subject_id' => 'unique:mongodb.panoptes_transcriptions_new, subject_id'];
        $values = ['subject_id' => (int) $subject_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if record exists.
        return $validator->fails();
    }
}

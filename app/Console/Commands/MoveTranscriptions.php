<?php

namespace App\Console\Commands;

use App\Models\ExpeditionStat;
use App\Models\Subject;
use App\Models\Transcription;
use Illuminate\Console\Command;

class MoveTranscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:transcriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes expedition ids on transcriptions.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subjects = Subject::all();

        foreach ($subjects as $subject)
        {
            $transcriptions = Transcription::where('subject_id', $subject->_id)->get();
            foreach ($transcriptions as $transcription)
            {
                $transcription->expedition_ids = $subject->expedition_ids;
                $transcription->save();
                echo "Saved transcription " . $transcription->_id . PHP_EOL;
            }
        }
    }
}

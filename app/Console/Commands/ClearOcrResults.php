<?php

namespace App\Console\Commands;

use App\Models\Subject;
use Artisan;
use Illuminate\Console\Command;

class ClearOcrResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:clear {projectId} {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear project expeditions ocr values.';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \MongoCursorException
     */
    public function handle()
    {
        $projectId = $this->argument('projectId');
        $expeditionId = $this->argument('expeditionId');

        $subjects = Subject::where('project_id', (int) $projectId)->limit(50)->get();
        foreach ($subjects as $subject) {
            $subject->ocr = '';
            $subject->save();
        }

        Artisan::call('lada-cache:flush');
    }
}

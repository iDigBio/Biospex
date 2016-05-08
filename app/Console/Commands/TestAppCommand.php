<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use App\Models\Subject;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    
    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * handle
     */
    public function handle()
    {
        $expeditions = Expedition::all();
        foreach ($expeditions as $expedition)
        {
            $subjects = Subject::where('expedition_ids', '=', (int) $expedition->id)->get();
            foreach ($subjects as $subject)
            {
                if(count($subject->expedition_ids) > 1)
                {
                    $subject->expedition_ids = [$expedition->id];
                    echo 'Updating subject' . PHP_EOL;
                    $subject->save();
                }
            }
        }
    }
}

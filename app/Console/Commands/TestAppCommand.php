<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $name = 'test:test';

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
     * Fire
     */
    public function fire()
    {
        /* Empty ocr values 
        $subjects = $this->subject->findByProjectId(6);
        foreach($subjects as $subject)
        {
            $subject->ocr = '';
            $subject->save();
        }
        return;
        */

    }
}

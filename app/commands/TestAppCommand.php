<?php

use Biospex\Repo\Subject\SubjectInterface;
use Illuminate\Console\Command;
use League\Csv\Reader;

class TestAppCommand extends Command {

    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    protected $reader;


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $file = storage_path('images.csv');

        $this->reader = Reader::createFromPath($file);
        $this->reader->setDelimiter(",");

        $i = 0;
        foreach ($this->reader->setOffset(1)->fetch() as $row)
        {
            if (empty($row[0]))
            {
                continue;
            }
            $subjects = Subject::where('occurrence.id', $row[0])->get();
            if (count($subjects) > 1) {
                $countArray[] = $row[0];
            }
            foreach ($subjects as $subject)
            {
                $i++;
                echo $subject->_id . " : " . $subject->occurrence->id . " : " . $subject->project_id . PHP_EOL;
            }

        }
        echo $i . " Records Found" . PHP_EOL;
        print_r($countArray);


        return;
    }

}

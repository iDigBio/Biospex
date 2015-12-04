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
    /**
     * @var SubjectInterface
     */
    private $subjectInterface;

    public function __construct(SubjectInterface $subjectInterface)
    {
        parent::__construct();
        $this->subjectInterface = $subjectInterface;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $file = storage_path('images.csv');

        $this->reader = Reader::createFromPath($file);
        $this->reader->setDelimiter(",");

        $header = $this->reader->fetchOne();

        foreach ($this->reader->setOffset(1)->fetch() as $row)
        {
            $combined = array_combine($header, $row);

            $result = Subject::where("project_id", 2)->where('occurrence.id', $combined['coreid'])->first();
            if (empty($result))
                continue;

            $subject = array_merge(json_decode(json_encode($result), true), $combined);

            print_r($subject);
            exit;
        }

        return;
    }

}

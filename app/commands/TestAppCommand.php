<?php

use Biospex\Repo\Header\HeaderInterface;
use Biospex\Repo\Project\ProjectInterface;
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
     * @var HeaderInterface
     */
    private $headerInterface;

    public function __construct(HeaderInterface $headerInterface)
    {
        parent::__construct();

        $this->headerInterface = $headerInterface;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $projects = Project::with(['expeditions.subjects'])->get();

        foreach($projects as $project) {
            $advertise = json_decode($project->advertise);
            $project->advertise = serialize($advertise);
            $project->save();

            /*
            $header = $this->headerInterface->getByProjectId($project->id);

            if (empty($header)) {
                continue;
            }

            foreach (json_decode($header->header) as $key => $value)
            {
                $newHeader['image'][] = $key;
            }

            $occurrenceHeader = [];
            foreach($project->expeditions as $expedition) {
                foreach ($expedition->subjects as $subject) {
                    $subject = $subject->toArray();
                    $occurrence = $subject['occurrence'];
                    unset($occurrence['_id']);
                    $occurrenceHeader = array_merge(array_diff(array_keys($occurrence), $occurrenceHeader), $occurrenceHeader);
                }
            }
            $newHeader['occurrence'] = $occurrenceHeader;

            $header->header = serialize($newHeader);
            $header->save();
            */
        }

        echo  "Done" . PHP_EOL;
        return;
    }
}

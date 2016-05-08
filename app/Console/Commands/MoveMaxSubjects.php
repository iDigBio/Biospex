<?php

namespace App\Console\Commands;

ini_set('memory_limit', '2048M');

use App\Models\Expedition;
use App\Models\Project;
use App\Models\Subject;

use Illuminate\Console\Command;

class MoveMaxSubjects extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subjects, creating new expeditions, if max over 1000';
    /**
     * @var \App\Repositories\Contracts\Expedition
     */

    private $expedition;

    private $projectId;

    private $subjects;

    private $ids;

    private $subjectChunk;


    /**
     * Create a new command instance.
     */
    public function __construct(\App\Repositories\Contracts\Expedition $expedition)
    {
        parent::__construct();
        $this->expedition = $expedition;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::all();

        foreach ($projects as $project)
        {
            echo $project->title . PHP_EOL;
            
            $this->processProject($project);
        }
    }

    public function processProject($project)
    {
        $this->projectId = $project->id;

        $expeditions = Expedition::where('project_id', $project->id)->get();

        $this->processExpeditions($expeditions);
    }

    /**
     * @param $expeditions
     */
    public function processExpeditions($expeditions)
    {
        foreach ($expeditions as $expedition)
        {
            echo $expedition->title . PHP_EOL;
            $this->setVariables($expedition);
            $this->processExpedition($expedition);
        }
    }

    public function setVariables($expedition)
    {
        echo 'Getting subjects' . PHP_EOL;
        $this->subjects = Subject::where('expedition_ids', '=', (int) $expedition->id)->get();
        $this->ids = array_column($this->subjects->toArray(), '_id');
        $this->subjectChunk = array_chunk($this->ids, 1000, true);
    }

    public function processExpedition($expedition)
    {
        echo 'Processing ' . $expedition->title . ' Subjects: ' . count($this->subjects) . PHP_EOL;
        
        $count = count($this->subjectChunk);
        
        if ($count > 1)
        {
            echo 'Splitting expedition' . PHP_EOL;
            $this->splitExpeditions($expedition, $count);
        }
        else
        {
            echo 'Saving original expedition' . PHP_EOL;
            $data = $this->createData($expedition, $this->subjectChunk[0]);
            $this->saveExpedition($data);
        }
    }

    public function splitExpeditions($expedition, $count)
    {
        for ($i = 0; $i < $count; $i++)
        {
            $data = $this->createData($expedition, $this->subjectChunk[$i], $i);
            $this->saveExpedition($data);
        }
    }

    public function createData($expedition, $chunk, $i = 0)
    {
        echo 'Create data with chunk count ' . count($chunk) . PHP_EOL;
        $data = [
            'project_id' => $this->projectId,
            'title' => $expedition->title . ' ' . str_random(10),
            'description' => $expedition->description,
            'keywords' => $expedition->keywords,
            'subjectIds' => implode(',', $chunk),
            'subjectCount' => count($chunk)
        ];

        $data = $i === 0 ? array_merge($data, ['id' => $expedition->id]) : $data;

        return $data;
    }

    public function saveExpedition($data)
    {
        if (array_key_exists('id', $data))
        {
            echo 'Updating Expedition' . PHP_EOL;
            $this->expedition->update($data);
        }
        else
        {
            echo 'Creating Expedition' . PHP_EOL;
            $this->expedition->create($data);
        }
    }
}

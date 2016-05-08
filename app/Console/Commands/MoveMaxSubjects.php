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
        $expeditions = $this->expedition->where('project_id', 1)->get();
        
        dd($expeditions);
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
            echo "Getting subjects" . PHP_EOL;

            $this->setVariables($expedition);
            $this->processExpedition($expedition);
        }
    }

    public function setVariables($expedition)
    {
        $this->subjects = Subject::where('expedition_ids', '=', (int) $expedition->id)->get();
        $this->ids = array_column($this->subjects->toArray(), '_id');
        $this->subjectChunk = array_chunk($this->ids, 1000, true);
    }

    public function processExpedition($expedition)
    {
        echo '  ' . $expedition->title . ' Subjects: ' . count($this->subjects) . PHP_EOL;
        
        $count = count($this->subjectChunk);
        
        if ($count > 1)
        {
            $this->splitExpeditions($expedition, $count);
        }
        else
        {
            $data = $this->createData($expedition, $this->subjectChunk[0]);
            $this->saveExpedition($data);
        }
    }

    public function splitExpeditions($expedition, $count)
    {
        for ($i = 0; $i < $count; $i++)
        {
            $data = $this->createData($expedition, $this->subjectChunk[$i]);
            $this->saveExpedition($data);
        }
    }

    public function createData($expedition, $chunk)
    {
        $data = [
            'project_id' => $this->projectId,
            'title' => $expedition->title . ' ' . str_random(10),
            'description' => $expedition->description,
            'keywords' => $expedition->keywords,
            'subjectIds' => implode(',', $chunk),
            'subjectCount' => count($chunk)
        ];

        $data = isset($expedition->id) ? array_merge($data, ['id' => $expedition->id]) : $data;

        return $data;
    }

    public function saveExpedition($data)
    {
        array_key_exists('id', $data) ? $this->expedition->update($data) : $this->expedition->create($data);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\Workflow;
use Illuminate\Support\Facades\DB;

class UpdateQueries extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';
    /**
     * @var Actor
     */
    private $actor;
    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * UpdateQueries constructor.
     * @param Actor $actor
     * @param Workflow $workflow
     */
    public function __construct(Actor $actor, Workflow $workflow)
    {
        parent::__construct();
        $this->actor = $actor;
        $this->workflow = $workflow;
    }

    /**
     * Fire
     */
    public function fire()
    {
        DB::statement("UPDATE actors SET title = 'Notes From Nature Original', class = 'NotesFromNatureOrig' WHERE actors.id = 1");
        DB::statement("UPDATE `actors` SET `title` = 'Notes From Nature Manifest', `class` = 'NotesFromNatureManifest' WHERE `actors`.`id` = 2");
        DB::statement("UPDATE workflows SET workflow = 'Notes From Nature Original' WHERE workflows.id = 2");
        DB::statement("UPDATE `workflows` SET `workflow` = 'Notes From Nature Manifest' WHERE `workflows`.`id` = 3");
        DB::statement("UPDATE `workflows` SET `workflow` = 'OCR -> Notes From Nature Original' WHERE `workflows`.`id` = 4");
        DB::statement("UPDATE `workflows` SET `workflow` = 'OCR -> Notes From Nature Manifest' WHERE `workflows`.`id` = 5");
        
        $actorData = [
            'title' => 'Notes From Nature CSV',
            'url' => 'http://www.notesfromnature.org/',
            'class' => 'NotesFromNatureCsv'  
        ];
        
        $actorNew = $this->actor->create($actorData);
        $ocrRecord = $this->actor->findByTitle('OCR');
        
        $workflowData = [
            [
                'workflow' => 'NotesFromNatureCsv'
            ],
            [
                'workflow' => 'OCR -> Notes From Nature Csv'
            ]
        ];

        foreach ($workflowData as $key => $data)
        {
            $workflow = $this->workflow->create($data);
            if ($key === 0)
            {
                $workflow->actors()->attach($actorNew->id);
            }
            else
            {
                $workflow->actors()->attach([$ocrRecord->id => ['order' => 0], $actorNew->id => ['order' => 1] ]);
            }
        }
    }

}
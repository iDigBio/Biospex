<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Repositories\Contracts\Expedition;
use Illuminate\Console\Command;

class UpdateExpeditionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Expedition $expedition)
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
        $expeditions = $this->expedition->all();
        $count = 0;
        foreach ($expeditions as $expedition)
        {
            $subjects = Subject::where('expedition_ids', '=', (int) $expedition->id)->get();
            $ids = array_column($subjects->toArray(), '_id');
            $count += count($ids);

            $data = [
                'id' => $expedition->id,
                'project_id' => $expedition->project_id,
                'title' => $expedition->title,
                'description' => $expedition->description,
                'keywords' => $expedition->keywords,
                'subjectIds' => implode(',', $ids),
                'subjectCount' => count($ids)
            ];

            $this->expedition->update($data);
        }

        echo $count . PHP_EOL;
    }
}

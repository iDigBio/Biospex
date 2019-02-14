<?php

namespace App\Console\Commands;

use App\Facades\CountHelper;
use App\Repositories\Interfaces\PanoptesTranscription;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Console\Commands\PanoptesTranscription
     */
    private $transcriptionContract;

    /**
     * Create a new job instance.
     */
    public function __construct(PanoptesTranscription $transcriptionContract)
    {
        parent::__construct();
        $this->transcriptionContract = $transcriptionContract;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $projectId = 13;
        /*
        $transcribers = collect($this->transcriptionContract->getUserTranscriptionCount($projectId))->sortByDesc('transcriptionCount');

        $transcriptions = \Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 240, function () use (
            $transcribers,
            $projectId
        ) {
            $plucked = collect(array_count_values($transcribers->pluck('transcriptionCount')->sort()->toArray()));

            return $plucked->flatMap(function ($users, $count) {
                return [['transcriptions' => $count, 'transcribers' => $users]];
            })->toJson();
        });
        \Storage::put('test1.json', $transcriptions);
        dd($transcriptions);
        */

        //$results = $this->transcriptionContract->getUserTranscriptionCount(13)->sortByDesc('transcriptionCount');
        $transcribers = CountHelper::getUserTranscriptionCount($projectId)->sortByDesc('transcriptionCount');

        //$count = $results->pluck('transcriptionCount')->sort()->toArray();
        $transcriptions = $transcribers->pluck('transcriptionCount')->pipe(function($transcribers){
            return collect(array_count_values($transcribers->sort()->toArray()));
        })->flatMap(function($users, $count){
            return [['transcriptions' => $count, 'transcribers' => $users]];
        })->toJson();
        \Storage::put('test2.json', $transcriptions);
        //$plucked = collect(array_count_values($transcribers->pluck('transcriptionCount')->sort()->toArray()));

        //dd($results->first()->transcriptionCount);
    }
}

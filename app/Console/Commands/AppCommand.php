<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\ExportQueue;
use App\Repositories\Interfaces\ExportQueueFile;
use App\Services\MongoDbService;
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
     * @var \App\Repositories\Interfaces\ExportQueue
     */
    private $exportQueueContract;

    /**
     * @var \App\Repositories\Interfaces\ExportQueueFile
     */
    private $exportQueueFileContract;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * AppCommand constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueue $exportQueueContract
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFileContract
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        ExportQueue $exportQueueContract,
        ExportQueueFile $exportQueueFileContract,
        MongoDbService $mongoDbService
    ) {
        parent::__construct();
        $this->exportQueueContract = $exportQueueContract;
        $this->exportQueueFileContract = $exportQueueFileContract;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     *
     */
    public function handle()
    {
        $results = $this->exportQueueFileContract->getFilesWithErrorsByQueueId(1);
        $remove = array_flip(['id', 'queue_id', 'error', 'created_at', 'updated_at']);
        $data = $results->map(function($file) use($remove){
            return array_diff_key($file->toArray(), $remove);
        });
        //dd(array_keys($data[0]));
        dd($data->toArray());

        /*
        $withRelations = ['nfnActor', 'stat'];

        $expedition = $this->expeditionContract->findWith($expeditionId, $withRelations);
        $expedition->nfnActor->pivot->state = 0;
        $expedition->nfnActor->pivot->total = $expedition->stat->local_subject_count;
        $expedition->nfnActor->pivot->processed = 0;
        $expedition->nfnActor->pivot->queued = 1;
        event('actor.pivot.regenerate', [$expedition->nfnActor]);

        dd($expedition->nfnActor);

          "id" => 2
          "title" => "Notes From Nature V2"
          "url" => "http://www.notesfromnature.org/"
          "class" => "NfnPanoptes"
          "private" => 0
          "created_at" => "2015-04-29 16:11:03"
          "updated_at" => "2016-07-07 18:26:00"
         */
    }
}
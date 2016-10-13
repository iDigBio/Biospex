<?php

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Repositories\Contracts\Expedition;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ExportPollCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var string
     */
    private $nfnActors;

    /**
     * @var \Illuminate\Foundation\Application|\Laravel\Lumen\Application|mixed
     */
    private $dispatcher;

    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * Create a new command instance.
     *
     * @param Expedition $expedition
     * @param Dispatcher $dispatcher
     * @internal param Actor $actor
     */
    public function __construct(Expedition $expedition, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->nfnActors = explode(',', Config::get('config.nfnActors'));
        $this->expedition = $expedition;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->expedition->skipCache()->with(['project.group', 'actors'])
            ->whereHasIn('actors', ['actor_id' => $this->nfnActors])
            ->whereHas('actors', ['state' => 0, 'error' => 0, 'queued' => 1])
            ->orderBy(['id' => 'asc'])
            ->get();

        if ($records->isEmpty())
        {
            $data = trans('pages.processing_empty');
            $this->dispatcher->fire(new PollExportEvent($data));

            return;
        }

        $data = [];
        $i = 0;
        foreach ($records as $record)
        {
            $message = ($i === 0) ?
                trans_choice('pages.export_processing', $record->actors[0]->pivot->processed, [
                    'title' => $record->title,
                    'processed' => $record->actors[0]->pivot->processed,
                    'total' => $record->actors[0]->pivot->total
                ]) :
                trans_choice('pages.export_queued', $i, [
                    'title' => $record->title,
                    'count' => $i
                ]);

            $data[] = [
                'groupUuid'       => $record->project->group->uuid,
                'message'         => $message
            ];

            $i++;
        }

        $this->dispatcher->fire(new PollExportEvent($data));
    }
}

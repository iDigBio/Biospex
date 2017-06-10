<?php

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Command;

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
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * Create a new command instance.
     *
     * @param ExpeditionContract $expeditionContract
     * @param Dispatcher $dispatcher
     * @internal param Actor $actor
     */
    public function __construct(ExpeditionContract $expeditionContract, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->nfnActors = explode(',', config('config.nfnActors'));
        $this->expeditionContract = $expeditionContract;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->expeditionContract->setCacheLifetime(0)
            ->with(['project.group', 'actors'])
            ->whereHas('actors', function ($query) {
                $query->whereIn('actor_id', $this->nfnActors);
                $query->where('state', 0)->where('error', 0)->where('queued', 0);
            })
            ->orderBy('id', 'asc')
            ->findAll();

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

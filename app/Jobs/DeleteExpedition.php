<?php

namespace App\Jobs;

use App\Models\Expedition;
use App\Repositories\Interfaces\Expedition as ExpeditionContact;
use App\Repositories\Interfaces\Subject;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class DeleteExpedition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Expedition
     */
    private $expedition;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {
        $this->expedition = $expedition;
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(
        ExpeditionContact $expeditionContract,
        Subject $subjectContract,
        MongoDbService $mongoDbService)
    {
        $expedition = $expeditionContract->findWith($this->expedition->id, ['downloads']);

        $expedition->downloads->each(function ($download){
            Storage::delete(config('config.export_dir').'/'.$download->file);
        });

        $mongoDbService->setCollection('pusher_transcriptions');
        $mongoDbService->deleteMany(['expedition_uuid' => $expedition->uuid]);

        $mongoDbService->setCollection('panoptes_transcriptions');
        $mongoDbService->deleteMany(['subject_expeditionId' => $expedition->id]);

        $subjects = $subjectContract->findSubjectsByExpeditionId($expedition->id);

        if ($subjects->isNotEmpty())
        {
            $subjectContract->detachSubjects($subjects, $expedition->id);
        }

        $expedition->delete();
    }
}

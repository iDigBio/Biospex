<?php

namespace App\Jobs;

use App\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExpeditionStatJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * ExpeditionStatJob constructor.
     *
     * @param $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = (int) $expeditionId;
        $this->onQueue(config('config.beanstalkd.stat'));
    }

    /**
     * Execute the job.
     *
     * @param Expedition $expedition
     */
    public function handle(Expedition $expedition)
    {
        $record = $expedition->findWith($this->expeditionId, ['stat']);
        $count = $expedition->getExpeditionSubjectCounts($this->expeditionId);

        $record->stat->subject_count = $count;
        $record->stat->transcriptions_total = transcriptions_total($count);
        $record->stat->transcriptions_completed = transcriptions_completed($this->expeditionId);
        $record->stat->percent_completed = transcriptions_percent_completed($record->stat->transcriptions_total, $record->stat->transcriptions_completed);

        $record->stat->save();
    }
}

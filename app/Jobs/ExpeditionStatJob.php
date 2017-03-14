<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExpeditionStatJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

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
    }

    /**
     * Execute the job.
     *
     * @param ExpeditionContract $expedition
     */
    public function handle(ExpeditionContract $expedition)
    {
        $record = $expedition->setCacheLifetime(0)->expeditionFindWith($this->expeditionId, 'stat');
        $count = $expedition->setCacheLifetime(0)->getExpeditionSubjectCounts($this->expeditionId);

        $record->stat->subject_count = $count;
        $record->stat->transcriptions_total = transcriptions_total($count);
        $record->stat->transcriptions_completed = transcriptions_completed($this->expeditionId);
        $record->stat->percent_completed = transcriptions_percent_completed($record->stat->transcriptions_total, $record->stat->transcriptions_completed);

        $record->stat->save();
    }
}

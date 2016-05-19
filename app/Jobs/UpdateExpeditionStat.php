<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionStat;
use App\Repositories\Contracts\Subject;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateExpeditionStat extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    /**
     * @var
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param Subject $subject
     * @param ExpeditionStat $expeditionStat
     */
    public function handle(Subject $subject, ExpeditionStat $expeditionStat)
    {
        $stat = $expeditionStat->findByExpeditionId($this->expeditionId);
        $count = $subject->getCountByExpeditionId($this->expeditionId);

        $stat->subject_count = $count;
        $stat->transcriptions_total = transcriptions_total($count);
        $stat->transcriptions_completed = transcriptions_completed($this->expeditionId);
        $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
        $stat->start_date = ($stat->start_date === null) ? Carbon::now()->toDateTimeString() : $stat->start_date;
        $stat->save();
    }
}

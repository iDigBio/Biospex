<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionStat;
use App\Repositories\Contracts\NfnClassification;
use App\Repositories\Contracts\Subject;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExpeditionStatJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var
     */
    private $projectId;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param $projectId
     * @param $expeditionId
     */
    public function __construct($projectId, $expeditionId)
    {
        $this->projectId = (int) $projectId;
        $this->expeditionId = (int) $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param Subject $subject
     * @param ExpeditionStat $expeditionStat
     */
    public function handle(Subject $subject, ExpeditionStat $expeditionStat)
    {
        $stat = $expeditionStat->skipCache()->where(['expedition_id' => $this->expeditionId])->first();
        $count = $subject->skipCache()->where(['expedition_ids' => $this->expeditionId])->count();
        
        $stat->subject_count = $count;
        $stat->transcriptions_total = transcriptions_total($count);
        $stat->transcriptions_completed = transcriptions_completed($this->expeditionId);
        $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
        $stat->start_date = (null === $stat->start_date) ? $this->getEarliestDate() : $stat->start_date;

        $stat->save();
    }

    /**
     * @return null|string
     */
    private function getEarliestDate()
    {
        $classification = app(NfnClassification::class);
        $record = $classification->skipCache()->where(['project_id' => $this->projectId, 'expedition_id' => $this->expeditionId])->orderBy(['started_at' => 'asc'])->first();
        
        if ($record === null)
        {
            return null;
        }

        return $record->started_at;
        
    }
}

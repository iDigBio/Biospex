<?php

namespace App\Jobs;

use App\Repositories\Contracts\ExpeditionStat;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\Transcription;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateExpeditionStat extends Job implements ShouldQueue
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
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param Subject $subject
     * @param ExpeditionStat $expeditionStat
     * @param Transcription $transcription
     */
    public function handle(Subject $subject, ExpeditionStat $expeditionStat, Transcription $transcription)
    {
        $stat = $expeditionStat->skipCache()->where(['expedition_id' => $this->expeditionId])->first();
        $count = $subject->skipCache()->where(['expedition_ids' => $this->expeditionId])->count();
        
        $stat->subject_count = $count;
        $stat->transcriptions_total = transcriptions_total($count);
        $stat->transcriptions_completed = transcriptions_completed($this->expeditionId);
        $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
        $stat->start_date = (null === $stat->start_date) ? $this->getEarliestDate($transcription) : $stat->start_date;

        $stat->save();
    }

    /**
     * @param Transcription $transcription
     * @return mixed
     */
    private function getEarliestDate(Transcription $transcription)
    {
        $record = $transcription->skipCache()->where(['project_id' => $this->projectId, 'expedition_id' => $this->expeditionId])->orderBy(['finished_at' => 'asc'])->first();
        
        if ($record === null)
        {
            return null;
        }

        $date = new \DateTime($record->finished_at);
        $date->sub(new \DateInterval('PT1H'));
        
        return $date->format('Y-m-d H:i:s');
        
    }
}

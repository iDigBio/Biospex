<?php 

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\OcrQueue;

class PollOcrEvent extends Event implements ShouldBroadcast
{

    use SerializesModels;

    public $data = [];
    
    private $ocrQueue;

    /**
     * PollOcrEvent constructor.
     *
     * @param OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
        $this->buildData();
    }

    /**
     * Set the name of the queue the event should be placed on.
     *
     * @return string
     */
    public function onQueue()
    {
        return Config::get('config.beanstalkd.event');
    }

    /**
     * Get the broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'app.polling';
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [Config::get('config.ocr_poll_channel')];
    }

    private function buildData()
    {
        $records = $this->ocrQueue->allWith(['project.group']);

        if ($records->isEmpty())
        {
            return;
        }
        
        $grouped = $records->groupBy('ocr_csv_id')->toArray();
        $totalSubjectsAhead = 0;
        $previousKey = null;
        foreach ($grouped as $key => $group)
        {
            if (null !== $previousKey)
            {
                $totalSubjectsAhead = +array_sum(array_column($grouped[$previousKey], 'subject_remaining'));
            }

            $previousKey = $key;

            $groupSubjectCount = array_sum(array_column($group, 'subject_count'));
            $groupSubjectRemaining = array_sum(array_column($group, 'subject_remaining'));

            $this->data[] = [
                'batchId'               => $key,
                'groupUuid'             => $group[0]['project']['group']['uuid'],
                'projectTitle'          => $group[0]['project']['title'],
                'totalSubjectsAhead'    => $totalSubjectsAhead,
                'groupSubjectRemaining' => $groupSubjectRemaining,
                'groupSubjectCount'     => $groupSubjectCount
            ];
        }
    }
}

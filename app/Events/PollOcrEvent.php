<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Config;

class PollOcrEvent extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $data = [];
    public $channels = [];

    /**
     * @var
     */
    private $records;

    /**
     * PollOcrEvent constructor.
     *
     * @param $records
     */
    public function __construct($records)
    {
        $this->buildData($records);
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
        return $this->channels;
    }

    private function buildData($records)
    {
        $grouped = $records->groupBy('project_id')->toArray();
        $totalSubjectsAhead = 0;
        $previousKey = null;
        foreach ($grouped as $key => $group)
        {
            if ($previousKey) {
                $totalSubjectsAhead =+ array_sum(array_column($grouped[$previousKey], 'subject_remaining'));
            }

            $previousKey = $key;

            $groupSubjectCount = array_sum(array_column($group, 'subject_count'));
            $groupSubjectRemaining = array_sum(array_column($group, 'subject_remaining'));

            $this->setChannel($group[0]['project']['group']['id']);

            $this->data[] = [
                'groupId' => $group[0]['project']['group']['id'],
                'projectTitle' => $group[0]['project']['title'],
                'totalSubjectsAhead' => $totalSubjectsAhead,
                'groupSubjectRemaining' => $groupSubjectRemaining,
                'groupSubjectCount' => $groupSubjectCount
            ];
        }

        return;
    }

    private function setChannel($groupId)
    {
        return in_array("channel-" . $groupId, $this->channels) ? null : $this->channels[]["channel-" . $groupId]; 
    }
}

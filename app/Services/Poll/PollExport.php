<?php

namespace App\Services\Poll;

use App\Events\PollExportEvent;

class PollExport
{

    /**
     * @var
     */
    private $total;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var
     */
    private $expeditionTitle;

    /**
     * @var
     */
    private $groupUuid;


    public function setGroupUuid($id)
    {
        $this->groupUuid = $id;
    }

    public function setExpeditionTitle($title)
    {
        $this->expeditionTitle = $title;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function updateCount()
    {
        $this->count++;

        if ($this->count % 10 === 0) {
            $data = [
                'groupUuid' => $this->groupUuid,
                'expeditionTitle' => $this->expeditionTitle,
                'message' => trans('expeditions.poll_export_count', [
                    ':count' => $this->count, ':total' => $this->total
                ])
            ];
            $this->sendMessage($data);
        }
    }

    public function sendCsvMessage()
    {
        $data = [
            'groupUuid' => $this->groupUuid,
            'expeditionTitle' => $this->expeditionTitle,
            'message' => trans('expeditions.poll_export_csv')
        ];
        $this->sendMessage($data);
    }

    /**
     * Clear any messages for poll event.
     */
    public function clearMessage()
    {
        $data = [
            'groupUuid' => $this->groupUuid,
            'expeditionTitle' => $this->expeditionTitle,
            'message' => ''
        ];
        $this->sendMessage($data);
    }

    /**
     * Fire export polling event.
     *
     * @param null $data
     */
    private function sendMessage($data = null)
    {
        event(new PollExportEvent($data));
    }

}
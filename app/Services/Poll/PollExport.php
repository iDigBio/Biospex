<?php

namespace App\Services\Poll;

use App\Events\PollExportEvent;
use Illuminate\Events\Dispatcher;

class PollExport
{

    /**
     * @var
     */
    protected $total;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var
     */
    private $expeditionTitle;

    /**
     * @var
     */
    private $groupUuid;

    /**
     * PollExport constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

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
        $this->dispatcher->fire(new PollExportEvent($data));
    }

}
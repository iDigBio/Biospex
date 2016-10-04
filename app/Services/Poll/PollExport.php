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
     * PollExport constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setProjectId()
    {

    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function updateCount()
    {
        $this->count++;
    }

    public function sendCountMessage($groupId, $projectTitle)
    {
        if ($this->count % 10 === 0) {
            $data = [
                'groupId' => $groupId,
                'projectTitle' => $projectTitle
            ];
            $this->sendMessage();
        }
    }

    public function sentCsvMessage()
    {

    }

    public function clearMessage()
    {
        $this->dispatcher->fire(new PollExportEvent());
    }
}
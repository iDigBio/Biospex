<?php

namespace App\Services\Actor;

use App\Services\Report\Report;

class ActorReportService extends ActorServiceBase
{

    /**
     * @var Report
     */
    public $report;

    /**
     * ActorReportService constructor.
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Report complete process.
     *
     * @param array $vars (title, message, groupId, attachmentName)
     * @param array $missingImages
     */
    public function processComplete($vars, array $missingImages = [])
    {
        $this->report->processComplete($vars, $missingImages);
    }
}
<?php
/**
 * OcrQueueCheck.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Console\Command;
use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Services\Report\Report;

class OcrQueueCheckCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ocrqueue:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check ocr queue table for invalid records";

    /**
     * Class constructor
     *
     * @param OcrQueueInterface $queue
     * @param Report $report
     */
    public function __construct(OcrQueueInterface $queue, Report $report)
    {
        parent::__construct();

        $this->queue = $queue;
        $this->report = $report;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $queues = $this->queue->allWith(['project.group.owner']);

        if (empty($queues)) {
            return;
        }

        foreach ($queues as $queue) {
            $this->report->addError(trans('emails.error_ocr_queue',
                [
                    'id'      => $queue->id,
                    'message' => trans('emails.error_ocr_stuck_queue', ['id' => $queue->id, 'tries' => $queue->tries]),
                    'url'     => ''
                ]));
        }

        $this->report->reportSimpleError();

        return;
    }
}

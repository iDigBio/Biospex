<?php namespace App\Services\Queue;

/**
 * WorkflowManagerService.php
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
use App\Repositories\Contracts\WorkflowManager;
use App\Services\Report\Report;
use Illuminate\Support\Facades\App;

class WorkflowManagerQueue extends QueueAbstract
{
    /**
     * Class constructor
     *
     * @param WorkflowManagerInterface $manager
     * @param Report $report
     */
    public function __construct(WorkflowManager $manager, Report $report)
    {
        $this->manager = $manager;
        $this->report = $report;
    }

    /**
     * Fire method.
     * @param $job
     * @param $data
     * @return mixed
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $manager = $this->manager->findWith($this->data['id'], ['expedition.actors']);

        if (empty($manager) || $this->checkProcess($manager)) {
            $this->delete();

            return;
        }

        $this->processActors($manager);

        $this->delete();

        return;
    }

    /**
     * @param $manager
     * @return bool
     */
    public function checkProcess($manager)
    {
        if ($manager->stopped == 1 || $manager->error == 1) {
            return true;
        }

        return false;
    }

    /**
     * @param $manager
     */
    public function processActors($manager)
    {
        foreach ($manager->expedition->actors as $actor) {
            try {
                $classNameSpace = 'App\Services\Actor\\' . $actor->class;
                $class = \App::make($classNameSpace);
                $class->setProperties($actor);
                $class->process();
                $manager->queue = 0;
                $manager->save();
            } catch (\Exception $e) {
                $manager->queue = 0;
                $manager->error = 1;
                $manager->save();
                $this->createError($manager, $actor, $e);
                break;
            }
        }
    }

    /**
     * Create and send error email
     *
     * @param $manager
     * @param $actor
     * @param $e
     */
    public function createError($manager, $actor, $e)
    {
        $this->report->addError(trans('emails.error_workflow_manager',
            [
                'class' => $actor->class,
                'id'    => $manager->id . ', Actor Id ' . $actor->id,
                'error' => $e->getFile() . " - " . $e->getLine() . ": " . $e->getMessage()
            ]));
        $this->report->reportSimpleError();
    }
}

<?php namespace Biospex\Services\Queue;

use Biospex\Repositories\Contracts\WorkflowManager;
use Biospex\Services\Report\Report;
use Exception;
use Illuminate\Support\Facades\App;

class WorkflowManagerQueue extends QueueAbstract
{
    /**
     * @var WorkflowManager
     */
    public $manager;

    /**
     * @var Report
     */
    public $report;

    /**
     * Class constructor
     *
     * @param WorkflowManager $manager
     * @param Report $report
     */
    public function __construct(
        WorkflowManager $manager,
        Report $report
    ) {
        $this->manager = $manager;
        $this->report = $report;
    }

    /**
     * Fire method.
     *
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
                $classNameSpace = 'Biospex\Services\Actor\\' . $actor->class;
                $class = App::make($classNameSpace);
                $class->setProperties($actor);
                $class->process();
                $manager->queue = 0;
                $manager->save();
            } catch (Exception $e) {
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

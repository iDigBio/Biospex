<?php


namespace App\Services\Model;

use App\Facades\Flash;
use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Workflow;
use Illuminate\Support\Facades\Notification;

class CommonVariables
{
    /**
     * @var \App\Repositories\Interfaces\Workflow
     */
    private $workflowContract;

    /**
     * CommonVariables constructor.
     *
     * @param \App\Repositories\Interfaces\Workflow $workflowContract
     */
    public function __construct(Workflow $workflowContract)
    {

        $this->workflowContract = $workflowContract;
    }


    public function setCommonVariables($user, $groups)
    {
        if (empty($groups)) {
            Flash::error(trans('messages.group_required'));

            return false;
        }

        $workflows = $this->workflowContract->getWorkflowSelect();
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;
        $resourcesSelect = GeneralHelper::getEnumValues('project_resources', 'type', true);

        return compact('workflows', 'statusSelect', 'selectGroups', 'resourcesSelect');
    }

    /**
     * Send notifications for new projects and actors.
     *
     * @param $projectId
     *
     */
    public function notifyActorContacts($projectId)
    {
        $nfnNotify = config('config.nfnNotify');
        $project = $this->findWith($projectId, ['workflow.actors.contacts']);

        $project->workflow->actors->reject(function ($actor) {
            return $actor->contacts->isEmpty();
        })->filter(function ($actor) use ($nfnNotify) {
            return isset($nfnNotify[$actor->id]);
        })->each(function ($actor) use ($project, $nfnNotify) {
            $class = '\App\Notifications\\'.$nfnNotify[$actor->id];
            if (class_exists($class)) {
                Notification::send($actor->contacts, new $class($project));
            }
        });
    }
}
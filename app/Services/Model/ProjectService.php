<?php


namespace App\Services\Model;

use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Workflow;
use Illuminate\Support\Facades\Notification;

class ProjectService
{
    /**
     * @var \App\Repositories\Interfaces\Workflow
     */
    private $workflowContract;

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * CommonVariables constructor.
     *
     * @param \App\Repositories\Interfaces\Workflow $workflowContract
     * @param \App\Repositories\Interfaces\Group $groupContract
     */
    public function __construct(
        Workflow $workflowContract,
        Group $groupContract
    )
    {

        $this->workflowContract = $workflowContract;
        $this->groupContract = $groupContract;
    }

    /**
     * Return workflow select options.
     */
    public function workflowSelectOptions()
    {
        return $this->workflowContract->getWorkflowSelect();
    }

    /**
     * Return group.
     *
     * @param $groupId
     * @return mixed
     */
    public function findGroup($groupId)
    {
        return $this->groupContract->find($groupId);
    }

    /**
     * Return select options for user's groups
     * @param $user
     * @return mixed
     */
    public function userGroupSelectOptions($user)
    {
        $groups = $this->groupContract->getUsersGroupsSelect($user);
        $select = ['' => '--Select--'] + $groups;

        return $select;
    }

    /**
     * Project status select options.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function statusSelectOptions()
    {
        return config('config.status_select');
    }

    /**
     * Send notifications for new projects and actors.
     *
     * @param $project
     *
     */
    public function notifyActorContacts($project)
    {
        $nfnNotify = config('config.nfnNotify');

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
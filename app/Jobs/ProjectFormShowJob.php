<?php

namespace App\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Actor;

class ProjectFormShowJob extends Job implements SelfHandling
{
    /**
     * Method being called.
     *
     * @var
     */
    public $method;

    /**
     * List of actors.
     *
     * @var
     */
    private $actors;

    /**
     * Status select from configuration.
     *
     * @var
     */
    private $statusSelect;

    /**
     * Select list for groups.
     *
     * @var
     */
    private $selectGroups;

    /**
     * Create a new job instance.
     *
     * @param $method
     * @param Group $group
     * @param Actor $actor
     * @internal param $operation
     * @internal param Project $project
     * @internal param Group $group
     * @internal param Actor $actor
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * Handle the job.
     *
     * @param  ProjectFormShowJob  $job
     * @return void
     */
    public function handle(Group $group, Actor $actor)
    {

        $this->group = $group;
        $this->actor = $actor;

        if (is_callable([$this, $this->method])) {
            return call_user_func([$this, $this->method]);
        }

        session_flash_push('error', trans('pages.method_does_not_exist'));

        return [false];
    }

    /**
     * Set common variables.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    protected function setCommonVariables()
    {
        $user = \Sentry::getUser();
        $allGroups = $user->isSuperUser() ? \Sentry::findAllGroups() : $user->getGroups();
        $groups = $this->group->selectOptions($allGroups);
        $this->actors = $this->actor->selectList();
        $this->statusSelect = config('config.status_select');

        if (empty($groups)) {
            session_flash_push('success', trans('groups.group_required'));
            return redirect()->route('groups.create');
        }

        $this->selectGroups = ['' => '--Select--'] + $groups;

        return;
    }

    /**
     * Create Project Form.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    protected function create()
    {
        $this->setCommonVariables();

        $count = is_null(\Input::old('targetCount')) ? 0 : \Input::old('targetCount');

        return [true, $this->selectGroups, $count, $this->actors, $this->statusSelect];
    }

    /**
     * Duplicate project.
     *
     * @param Project $repo
     * @return array
     */
    protected function duplicate(Project $repo)
    {
        $id = \Route::input('projects');
        $project = $repo->findWith($id, ['group']);

        $this->setCommonVariables();

        $workflowCheck = '';
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);

        return [true, $this->selectGroups, $project, $count, $this->actors, $this->statusSelect, $workflowCheck];
    }
}

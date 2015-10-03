<?php

namespace App\Services\Common;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Routing\UrlGenerator;
use Cartalyst\Sentry\Sentry;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Actor;

class ProjectService
{
    /**
     * @var Sentry
     */
    protected $sentry;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var Actor
     */
    protected $actor;

    /**
     * Construct.
     *
     * @param Sentry $sentry
     * @param Request $request
     * @param Router $router
     * @param UrlGenerator $url
     * @param Repository $config
     * @param Group $group
     * @param Project $project
     * @param Actor $actor
     */
    public function __construct(
        Sentry $sentry,
        Request $request,
        Router $router,
        UrlGenerator $url,
        Repository $config,
        Group $group,
        Project $project,
        Actor $actor
    ) {
        $this->sentry = $sentry;
        $this->request = $request;
        $this->router = $router;
        $this->url = $url;
        $this->config = $config;
        $this->group = $group;
        $this->project = $project;
        $this->actor = $actor;
    }

    /**
     * Build information for projects index page.
     *
     * @return array
     */
    public function showIndex()
    {
        $user = $this->sentry->getUser();
        $isSuperUser = $user->isSuperUser();
        $allGroups = $isSuperUser ? $this->sentry->findAllGroups() : $user->getGroups();
        $groups = $this->group->findAllGroupsWithProjects($allGroups);

        return compact('groups', 'user', 'isSuperUser');
    }

    /**
     * Show new project form.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function createForm()
    {
        $vars = $this->setCommonVariables();
        $count = is_null($this->request->old('targetCount')) ? 0 : $this->request->old('targetCount');

        return array_merge($vars, ['count' => $count]);
    }

    /**
     * Create a project.
     *
     * @param $request
     * @return mixed
     */
    public function store($request)
    {
        return $this->project->create($request->all());
    }

    /**
     * Build variables for displaying project.
     *
     * @return array
     */
    public function show()
    {
        $id = $this->router->input('projects');
        $project = $this->project->findWith($id, ['group', 'expeditions.downloads', 'expeditions.actors', 'expeditions.actorsCompletedRelation']);
        $user = $this->sentry->getUser();
        $isOwner = ($user->id == $project->group->user_id || $user->isSuperUser()) ? true : false;

        return compact('user', 'isOwner', 'project');
    }

    /**
     * Build variables for duplicating project.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function duplicate()
    {
        $id = $this->router->input('projects');
        $project = $this->project->findWith($id, ['group', 'actors']);

        if ( ! $project)
        {
            session_flash_push('error', trans('pages.project_repo_error'));
            return false;
        }

        $vars = $this->setCommonVariables();
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $workflowCheck = '';

        return array_merge($vars, ['count' => $count, 'workflowCheck' => $workflowCheck]);
    }

    /**
     * Build variables for editing project.
     *
     * @return array
     */
    public function edit()
    {
        $id = $this->router->input('projects');
        $project = $this->project->findWith($id, ['group', 'actors', 'expeditions.workflowManager']);

        $workflowCheck = '';
        foreach ($project->expeditions as $expedition) {
            $workflowCheck = is_null($expedition->workflowManager) ? '' : 'readonly';
        }

        $vars = $this->setCommonVariables();
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $cancel = $this->url->previous();

        return array_merge($vars, ['project' => $project, 'count' => $count, 'workflowCheck' => $workflowCheck, 'cancel' => $cancel]);
    }

    /**
     * Update project.
     *
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        return $this->project->update($request->all());
    }

    /**
     * Show advertise page and populate advertise field it necessary.
     *
     * @return mixed
     */
    public function advertise()
    {
        $project = $this->project->find($this->router->input('projects'));

        if (empty($project->advertise)) {
            $project->advertise = json_decode(json_encode($project), true);
            $project->save();
        }

        return $project;
    }

    /**
     * Downlad advertise json.
     *
     * @return mixed
     */
    public function advertiseDownload()
    {
        return $this->project->find($this->router->input('projects'));
    }

    /**
     * Destroy project.
     *
     * @return bool
     */
    public function destroy()
    {
        $id = $this->router->input('projects');
        $project = $this->project->findWith($id, ['group']);
        $user = $this->user->getUser();
        $isSuperUser = $user->isSuperUser();
        $isOwner = ($user->id == $project->group->user_id || $isSuperUser) ? true : false;

        if ($isOwner) {
            $this->project->destroy($id);
            session_flash_push('success', trans('projects.project_destroyed'));

            return true;
        }

        session_flash_push('error', trans('projects.project_destroy_error'));

        return false;
    }


    /**
     * Set common variables.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    protected function setCommonVariables()
    {
        $user = $this->sentry->getUser();
        $allGroups = $user->isSuperUser() ? $this->sentry->findAllGroups() : $user->getGroups();
        $groups = $this->group->selectOptions($allGroups);

        if (empty($groups)) {
            session_flash_push('success', trans('groups.group_required'));

            return redirect()->route('groups.create');
        }

        $actors = $this->actor->selectList();
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;

        return compact('actors', 'statusSelect', 'selectGroups');
    }
}


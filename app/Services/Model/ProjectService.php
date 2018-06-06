<?php

namespace App\Services\Model;

use App\Facades\Flash;
use App\Facades\GeneralHelper;
use App\Models\ProjectResource as ProjectResourceModel;
use App\Repositories\Interfaces\ProjectResource;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\User;
use App\Repositories\Interfaces\Workflow;
use App\Services\File\FileService;
use Illuminate\Support\Facades\Notification;
use JavaScript;

class ProjectService
{
    /**
     * @var Project
     */
    private $projectContract;

    /**
     * @var Workflow
     */
    private $workflowContract;

    /**
     * @var Group
     */
    private $groupContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * @var Subject
     */
    private $subjectContract;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var Expedition
     */
    private $expeditionContract;

    /**
     * @var OcrQueueService
     */
    private $ocrQueueService;

    /**
     * @var \App\Repositories\Interfaces\ProjectResource
     */
    private $projectResource;

    /**
     * ProjectService constructor.
     *
     * @param Project $projectContract
     * @param Workflow $workflowContract
     * @param Group $groupContract
     * @param User $userContract
     * @param Subject $subjectContract
     * @param FileService $fileService
     * @param Expedition $expeditionContract
     * @param OcrQueueService $ocrQueueService
     * @param \App\Repositories\Interfaces\ProjectResource $projectResource
     */
    public function __construct(
        Project $projectContract,
        Workflow $workflowContract,
        Group $groupContract,
        User $userContract,
        Subject $subjectContract,
        FileService $fileService,
        Expedition $expeditionContract,
        OcrQueueService $ocrQueueService,
        ProjectResource $projectResource
    ) {
        $this->projectContract = $projectContract;
        $this->workflowContract = $workflowContract;
        $this->groupContract = $groupContract;
        $this->userContract = $userContract;
        $this->subjectContract = $subjectContract;
        $this->fileService = $fileService;
        $this->expeditionContract = $expeditionContract;
        $this->ocrQueueService = $ocrQueueService;
        $this->projectResource = $projectResource;
    }

    /**
     * Find group for project by group id.
     *
     * @param $groupId
     * @return mixed
     */
    public function findGroup($groupId)
    {
        return $this->groupContract->find($groupId);
    }

    /**
     * Find project with attributes.
     *
     * @param $projectId
     * @param array $with
     * @param bool $trashed
     * @return mixed
     */
    public function findWith($projectId, array $with = [], $trashed = false)
    {
        return $this->projectContract->getProjectByIdWith($projectId, $with, $trashed);
    }

    /**
     * @return mixed
     */
    public function getallProjects()
    {
        return $this->projectContract->all();
    }

    /**
     * @return mixed
     */
    public function getTrashedProjects()
    {
        return $this->projectContract->getOnlyTrashed();
    }

    /**
     * Set common variables.
     *
     * @param $user
     * @return array|bool
     */
    public function setCommonVariables($user)
    {
        $groups = $this->groupContract->getUsersGroupsSelect($user);

        if (empty($groups)) {
            Flash::error(trans('groups.group_required'));

            return false;
        }

        $workflows = $this->workflowContract->getWorkflowSelect();
        $statusSelect = config('config.status_select');
        $selectGroups = ['' => '--Select--'] + $groups;
        $resourcesSelect = GeneralHelper::getEnumValues('project_resources', 'type', true);

        return compact('workflows', 'statusSelect', 'selectGroups', 'resourcesSelect');
    }

    /**
     * @param $projectId
     * @param null $expeditionId
     */
    public function processOcr($projectId, $expeditionId = null)
    {

        $this->ocrQueueService->processOcr($projectId, $expeditionId) ? Flash::success(trans('expeditions.ocr_process_success')) : Flash::warning(trans('expeditions.ocr_process_error'));
    }

    /**
     * Get project list by group for logged in user.
     *
     * @param $user
     * @param $trashed
     * @return mixed
     */
    public function getUserProjectListByGroup($user, $trashed = false)
    {
        return $this->groupContract->getUserProjectListByGroup($user, $trashed);
    }

    /**
     * Show project.
     *
     * @param $projectId
     * @param bool $trashed
     * @return mixed
     */
    public function getProjectExpeditions($projectId, $trashed = false)
    {
        $with = [
            'downloads',
            'actors',
            'stat',
        ];

        return $this->expeditionContract->findExpeditionsByProjectIdWith($projectId, $with, $trashed);
    }

    /**
     * Create a project.
     *
     * @param $attributes
     * @return bool|mixed
     */
    public function createProject($attributes)
    {
        $project = $this->projectContract->create($attributes);

        $resources = collect($attributes['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->map(function ($resource) {
            return new ProjectResourceModel($resource);
        });

        $project->resources()->saveMany($resources->all());

        if ($project) {
            $this->notifyActorContacts($project->id);

            Flash::success(trans('projects.project_created'));

            return $project;
        }

        Flash::error(trans('projects.project_save_error'));

        return false;
    }

    /**
     * Send notifications for new projects and actors.
     *
     * @param $projectId
     *
     */
    private function notifyActorContacts($projectId)
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

    /**
     * Duplicate a project.
     *
     * @param $projectId
     * @return array|bool
     */
    public function duplicateProject($projectId)
    {
        $project = $this->findWith($projectId, ['group', 'expeditions.workflowManager']);

        if (! $project) {
            Flash::error(trans('pages.project_repo_error'));

            return false;
        }

        $common = $this->setCommonVariables(request()->user());
        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return $variables;
    }

    /**
     * Edit project.
     *
     * @param $projectId
     * @return array|bool
     */
    public function editProject($projectId)
    {
        $project = $this->findWith($projectId, ['group', 'nfnWorkflows', 'resources']);

        if (! $project) {
            Flash::error(trans('pages.project_repo_error'));

            return false;
        }

        $workflowEmpty = ! isset($project->nfnWorkflows) || $project->nfnWorkflows->isEmpty();
        $common = $this->setCommonVariables(request()->user());

        $variables = array_merge($common, ['project' => $project, 'workflowEmpty' => $workflowEmpty]);

        return $variables;
    }

    /**
     * Update Project.
     *
     * @param $attributes
     * @param $projectId
     * @return mixed
     */
    public function updateProject($attributes, $projectId)
    {
        $attributes['slug'] = null;
        $project = $this->projectContract->update($attributes, $projectId);

        $resources = collect($attributes['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->reject(function ($resource) {
            return empty($resource['id']) ? false : $this->updateProjectResource($resource);
        })->map(function ($resource) {
            return new ProjectResourceModel($resource);
        });

        $project->resources()->saveMany($resources->all());

        $project ? Flash::success(trans('projects.project_updated')) : Flash::error(trans('projects.project_updated_error'));

        return;
    }

    /**
     * Update project resource.
     *
     * @param $resource
     * @return bool
     */
    public function updateProjectResource($resource)
    {
        $record = $this->projectResource->find($resource['id']);
        $record->type = $resource['type'];
        $record->name = $resource['name'];
        $record->description = $resource['description'];
        if (isset($resource['download'])) {
            $record->download = $resource['download'];
        }

        $record->save();

        return true;
    }

    /**
     * Filter or delete resource.
     *
     * @param $resource
     * @return bool
     */
    public function filterOrDeleteResources($resource)
    {
        if ($resource['type'] === null)
        {
            return true;
        }

        if ($resource['type'] === 'delete')
        {
            $this->projectResource->delete($resource['id']);

            return true;
        }

        return false;
    }

    /**
     * Explore project page.
     *
     * @param $projectId
     * @return mixed
     */
    public function explore($projectId)
    {
        JavaScript::put([
            'projectId'    => $projectId,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.explore', [$projectId]),
            'exportUrl'    => route('web.grids.project.export', [$projectId]),
            'showCheckbox' => true,
            'explore'      => true,
        ]);

        return $this->subjectContract->getSubjectAssignedCount($projectId);
    }

    /**
     * Delete project.
     *
     * @param $project
     * @return bool
     */
    public function deleteProject($project)
    {
        try {
            if ($project->nfnWorkflows->isNotEmpty()) {
                Flash::error(trans('expeditions.expedition_process_exists'));

                return false;
            }

            $this->projectContract->delete($project);
            Flash::success(trans('projects.project_deleted'));

            return true;
        } catch (\Exception $e) {
            Flash::error(trans('projects.project_delete_error'));

            return false;
        }
    }

    /**
     * Destory project.
     *
     * @param $project
     */
    public function destroyProject($project)
    {
        try {
            $project->expeditions->each(function ($expedition) {
                $expedition->downloads->each(function ($download) {
                    $this->fileService->filesystem->delete(config('config.nfn_export_dir').'/'.$download->file);
                });
            });

            if (! $project->subjects->isEmpty()) {
                $project->subjects()->timeout(-1)->forceDelete();
            }

            $this->projectContract->destroy($project);

            Flash::success(trans('projects.project_destroyed'));

            return;
        } catch (\Exception $e) {
            Flash::error(trans('projects.project_destroy_error'));

            return;
        }
    }

    /**
     * Restore Project.
     *
     * @param $project
     * @return mixed
     */
    public function restoreProject($project)
    {
        return $this->projectContract->restore($project) ? Flash::success(trans('projects.project_restored')) : Flash::error(trans('projects.project_restored_error'));
    }
}


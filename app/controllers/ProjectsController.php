<?php
/**
 * ProjectsController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Form\Project\ProjectForm;
use Biospex\Repo\Group\GroupInterface;
use Biospex\Repo\User\UserInterface;
use Biospex\Repo\Import\ImportInterface;
use Biospex\Repo\Actor\ActorInterface;

class ProjectsController extends BaseController {

	/**
     * @var Biospex\Repo\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var Biospex\Form\Project\ProjectForm
     */
    protected $projectForm;

    /**
     * @var Biospex\Repo\Group\GroupInterface
     */
    protected $group;

    /**
     * @var Biospex\Repo\User\UserInterface
     */
    protected $user;

	/**
	 * @var ActorInterface
	 */
	protected $actor;

	/**
	 * Instantiate a new ProjectsController
	 *
	 * @param Sentry $sentry
	 * @param ProjectInterface $project
	 * @param ProjectForm $projectForm
	 * @param GroupInterface $group
	 * @param UserInterface $user
	 * @param ImportInterface $import
	 * @param ActorInterface $actor
	 */
    public function __construct(
        ProjectInterface $project,
        ProjectForm $projectForm,
        GroupInterface $group,
        UserInterface $user,
        ImportInterface $import,
		ActorInterface $actor
    )
    {
        $this->project = $project;
        $this->projectForm = $projectForm;
        $this->group = $group;
        $this->user = $user;
        $this->import = $import;
		$this->actor = $actor;

		// Establish Filters
		$this->beforeFilter('auth');
        $this->beforeFilter('csrf', ['on' => 'post']);
        $this->beforeFilter('hasProjectAccess:project_view', ['only' => ['show', 'advertiseDownload']]);
        $this->beforeFilter('hasProjectAccess:project_edit', ['only' => ['edit', 'update', 'data']]);
        $this->beforeFilter('hasProjectAccess:project_delete', ['only' => ['destroy']]);

    }

    /**
	 * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
	 *
	 * @return Response
	 */
	public function index()
    {
		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
		$allGroups = $isSuperUser ? $this->group->findAllGroups() : $user->getGroups();
		$groups = $this->group->findAllGroupsWithProjects($allGroups);

        return View::make('projects.index', compact('groups', 'user', 'isSuperUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
	{
		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
		$allGroups = $isSuperUser ? $this->group->findAllGroups() : $user->getGroups();
		$groups = $this->group->selectOptions($allGroups);
		$actors = $this->actor->selectList();
        $statusSelect = \Config::get('config.statusSelect');

		if (empty($groups))
		{
			Session::flash('success', trans('groups.group_required'));
			return Redirect::action('GroupsController@create');
		}

		$cancel = URL::previous();
		$selectGroups = ['' => '--Select--'] + $groups;
        $count = is_null(Input::old('targetCount')) ? 0 : Input::old('targetCount');
        $create =  Route::currentRouteName() == 'projects.create' ? true : false;

		return View::make('projects.create', compact('cancel', 'selectGroups', 'count', 'create', 'actors', 'statusSelect'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store()
	{
        // Form Processing
        $project = $this->projectForm->save(Input::all());

        if($project)
        {
            // Success!
            Session::flash('success', trans('projects.project_created'));
            return Redirect::action('ProjectsController@show', [$project->id]);

        } else {
            Session::flash('error', trans('projects.project_save_error'));
            return Redirect::action('ProjectsController@create')
                ->withInput()
                ->withErrors($this->projectForm->errors());
        }
	}

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
	{
		$project = $this->project->findWith($id, ['group', 'expeditions.downloads', 'expeditions.actors', 'expeditions.actorsCompletedRelation']);
		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
        $isOwner = ($user->id == $project->group->user_id || $isSuperUser) ? true : false;
		
        return View::make('projects.show', compact('user', 'isOwner', 'project'));
	}

    /**
     * Create duplicate project
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function duplicate($id)
    {
		$project = $this->project->findWith($id, ['group']);

		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
		$allGroups = $isSuperUser ? $this->group->findAllGroups() : $user->getGroups();
		$groups = $this->group->selectOptions($allGroups);
		$actors = $this->actor->selectList();
        $statusSelect = \Config::get('config.statusSelect');
		$workflowCheck = '';

		$selectGroups = ['' => '--Select--'] + $groups;
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $create =  Route::currentRouteName() == 'projects.create' ? true : false;
		$cancel = URL::previous();

		return View::make('projects.clone', compact('selectGroups', 'project', 'count', 'create', 'cancel', 'actors', 'statusSelect', 'workflowCheck'));
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\View\View
	 */
	public function edit($id)
	{
		$project = $this->project->findWith($id, ['group', 'actors', 'expeditions.workflowManager']);
		$workflowCheck = '';
		foreach ($project->expeditions as $expedition)
		{
			$workflowCheck = is_null($expedition->workflowManager) ? '' : 'readonly';
		}

		$actors = $this->actor->selectList();
        $statusSelect = \Config::get('config.statusSelect');

		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
		$allGroups = $isSuperUser ? $this->group->findAllGroups() : $user->getGroups();
		$groups = $this->group->selectOptions($allGroups);

		$selectGroups = ['' => '--Select--'] + $groups;
		$count = is_null($project->target_fields) ? 0 : count($project->target_fields);
		$create =  Route::currentRouteName() == 'projects.create' ? true : false;
		$cancel = URL::previous();

		return View::make('projects.edit', compact('project', 'actors', 'statusSelect', 'workflowCheck', 'selectGroups', 'count', 'create', 'cancel'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
	{
        // Form Processing
        $result = $this->projectForm->update(Input::all());
        $project = $this->project->find($id);

        if($result)
        {
            // Success!
            Session::flash('success', trans('projects.project_updated'));
            return Redirect::action('projects.show', [$project->id]);

        } else {
            Session::flash('error', trans('projects.project_save_error'));
            return Redirect::route('projects.edit', [$project->id])
                ->withInput()
                ->withErrors( $this->projectForm->errors() );
        }
	}

    /**
     * Add data to project
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function data($id)
    {
		$project = $this->project->findWith($id, ['group']);
		$cancel = URL::previous();
        return View::make('projects.add', compact('project', 'cancel'));
    }

    /**
     * Advertise
     */
    public function advertise($id)
    {
        $project = $this->project->find($id);

        if (empty($project->advertise))
        {
            $project->advertise = json_decode(json_encode($project), true);
            $project->save();
        }

        return View::make('projects.advertise', compact('project'));

    }

    /**
     * Advertise download
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function advertiseDownload($id)
    {
        $project = $this->project->find($id);

        return Response::make($project->advertise, '200', array(
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ));
    }

    /**
     * Upload data file
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($id)
    {
        $file = Input::file('file');

        if (empty($file))
        {
            Session::flash('error', trans('projects.file_required'));
            return Redirect::route('projects.data', [$id]);
        }

        $filename = $file->getClientOriginalName();
        $directory = Config::get('config.subjectsImportDir');

        try
        {
            Input::file('file')->move($directory, $filename);
			$user = $this->user->getUser();
            $import = $this->import->create([
                            'user_id' => $user->id,
                            'project_id' => $id,
                            'file' => $filename
                        ]);

            Queue::push('Biospex\Services\Queue\SubjectsImportService', ['id' => $import->id], \Config::get('config.beanstalkd.subjectsImport'));
        }
        catch(Exception $e)
        {
            Session::flash('error', trans('projects.upload_error'));
            return Redirect::route('projects.data', [$id]);
        }

        Session::flash('success', trans('projects.upload_success'));
        return Redirect::route('projects.show', [$id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
	{
        $project = $this->project->findWith($id, ['group']);
		$user = $this->user->getUser();
		$isSuperUser = $user->isSuperUser();
        $isOwner = ($user->id == $project->group->user_id || $isSuperUser) ? true : false;
        if ($isOwner)
        {
            $this->project->destroy($id);
            Session::flash('success', trans('projects.project_destroyed'));

            return Redirect::route('projects.index');
        }

        Session::flash('error', trans('projects.project_destroy_error'));
        return Redirect::route('projects.index');
	}
}

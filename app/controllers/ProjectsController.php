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
     * Instantiate a new ProjectsController
     */
    public function __construct(
        ProjectInterface $project,
        ProjectForm $projectForm,
        GroupInterface $group,
        UserInterface $user,
        ImportInterface $import
    )
    {
        $this->project = $project;
        $this->projectForm = $projectForm;
        $this->group = $group;
        $this->user = $user;
        $this->import = $import;

        // Establish Filters
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('guest', array('only' => array('all')));
        $this->beforeFilter('hasProjectAccess:project_view', array('only' => array('show', 'index')));
        $this->beforeFilter('hasProjectAccess:project_edit', array('only' => array('edit', 'update', 'data')));
        $this->beforeFilter('hasProjectAccess:project_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasProjectAccess:project_create', array('only' => array('create', 'data')));
    }

    /**
     * Show all projects grouped by groups the user belongs to
     *
     * @return \Illuminate\View\View
     */
    public function all()
    {
        $user = $this->user->getUser();
        $groups = $user->isSuperUser() ? $this->group->all() : $user->getGroups();
        foreach ($groups as $group)
        {
            if ($group->name == 'Users' || $group->name == 'Admins') continue;
            $groupProjects[$group->id] = $group->projects;
            $groupNames[$group->id] = $group->name;
        }

        return View::make('projects.all', compact('groupProjects', 'groupNames'));
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
    {
        return Redirect::action('ProjectsController@all');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $groupId
     * @return \Illuminate\View\View
     */
    public function create($groupId)
	{
        $user = $this->user->getUser();
        $group = $this->group->find($groupId);
        $count = is_null(Input::old('targetCount')) ? 0 : Input::old('targetCount');
        $create =  Route::currentRouteName() == 'groups.projects.create' ? true : false;
        return View::make('projects.create', compact('user', 'group', 'count', 'create'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($groupId)
	{
        // Form Processing
        $project = $this->projectForm->save(Input::all());

        if($project)
        {
            // Success!
            Session::flash('success', trans('projects.project_created'));
            return Redirect::action('ProjectsController@show', array($project->group_id, $project->id));

        } else {
            Session::flash('error', trans('projects.project_save_error'));
            return Redirect::action('ProjectsController@create', $groupId)
                ->withInput()
                ->withErrors($this->projectForm->errors());
        }
	}

    /**
     * Display the specified resource.
     *
     * @param $groupId
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($groupId, $id)
	{
        $project = $this->project->find($id);
        $expeditions = $project->expedition;
        $imgUrl = !empty($project->logo->url) ? $project->logo->url('normal') : asset(Config::get('config.defaultImg'));
        return View::make('projects.show', compact('project', 'expeditions', 'imgUrl'));
	}

    /**
     * Create duplicate project
     *
     * @param $groupId
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function duplicate($groupId, $projectId)
    {
        $user = $this->user->getUser();
        $project = $this->project->findWith($projectId, ['group']);
        $group = $project->group;
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $create =  Route::currentRouteName() == 'groups.projects.create' ? true : false;

        return View::make('projects.clone', compact('user', 'group', 'project', 'count', 'create'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $groupId
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function edit($groupId, $projectId)
	{
        $project = $this->project->find($projectId);
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $create =  Route::currentRouteName() == 'groups.projects.create' ? true : false;

        return View::make('projects.edit', compact('project', 'count', 'create'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $groupId
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($groupId, $id)
	{
        // Form Processing
        $project = $this->projectForm->update(Input::all());

        if($project)
        {
            // Success!
            Session::flash('success', trans('projects.project_updated'));
            return Redirect::action('groups.projects.show', array($groupId, $id));

        } else {
            Session::flash('error', trans('projects.project_save_error'));
            return Redirect::route('groups.projects.edit', array($groupId, $id))
                ->withInput()
                ->withErrors( $this->projectForm->errors() );
        }
	}

    /**
     * Add data to project
     *
     * @param $groupId
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function data($groupId, $projectId)
    {
        $project = $this->project->find($projectId);
        return View::make('projects.add', compact('project'));
    }

    /**
     * Advertise
     */
    public function advertise()
    {

    }

    /**
     * Upload data file
     *
     * @param $groupId
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($groupId, $projectId)
    {
        $file = Input::file('file');

        if (empty($file))
        {
            Session::flash('error', trans('projects.file_required'));
            return Redirect::route('addData', array($groupId, $projectId));
        }

        $filename = str_random(8) . '.' . $file->getClientOriginalExtension();
        $directory = Config::get('config.dataDir');

        try
        {
            Input::file('file')->move($directory, $filename);
            $user = $this->user->getUser();
            $this->import->create(array('user_id' => $user->id,'project_id' => $projectId, 'file' => $filename));
        }
        catch(Exception $e)
        {
            Session::flash('error', trans('projects.upload_error'));
            return Redirect::route('addData', array($groupId, $projectId));
        }

        Session::flash('success', trans('projects.upload_success'));
        return Redirect::route('groups.projects.show', array($groupId, $projectId));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $groupId
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($groupId, $projectId)
	{
        if ($this->project->destroy($projectId))
        {
            Session::flash('success', trans('projects.project_destroyed'));
            return Redirect::action('ProjectsController@all');
        }
        else
        {
            Session::flash('error', trans('projects.project_destroy_error'));
            return Redirect::action('ProjectsController@all');
        }
	}

}

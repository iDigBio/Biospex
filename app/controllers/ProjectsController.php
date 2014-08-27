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
        $this->beforeFilter('guest', array('only' => array('index')));
        $this->beforeFilter('hasProjectAccess:project_view', array('only' => array('show')));
        $this->beforeFilter('hasProjectAccess:project_edit', array('only' => array('edit', 'update', 'data')));
        $this->beforeFilter('hasProjectAccess:project_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasProjectAccess:project_create', array('only' => array('data')));

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
		$user = Sentry::getUser();
		$isSuperUser = $user->isSuperUser();
        $groups = $this->group->findAllGroups($user, $isSuperUser);

        return View::make('projects.index', compact('groups', 'user', 'isSuperUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
	{
		$cancel = URL::previous();
        $groups = ['' => '--Select--'] + $this->group->selectOptions(false);
        $count = is_null(Input::old('targetCount')) ? 0 : Input::old('targetCount');
        $create =  Route::currentRouteName() == 'projects.create' ? true : false;

        return View::make('projects.create', compact('cancel', 'groups', 'count', 'create'));
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
        $project = $this->project->findWith($id, ['group']);
        $expeditions = $project->expedition;
		$user = Sentry::getUser();
		$isSuperUser = $user->isSuperUser();
        $isOwner = ($user->id == $project->group->user_id || $isSuperUser) ? true : false;

        return View::make('projects.show', compact('isOwner', 'project', 'expeditions'));
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
        $groups = ['' => '--Select--'] + $this->group->selectOptions(false);
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $create =  Route::currentRouteName() == 'projects.create' ? true : false;
		$cancel = URL::previous();

        return View::make('projects.clone', compact('groups', 'project', 'count', 'create', 'cancel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
	{
        $project = $this->project->find($id);
        $groups = ['' => '--Select--'] + $this->group->selectOptions(false);
        $count = is_null($project->target_fields) ? 0 : count($project->target_fields);
        $create =  Route::currentRouteName() == 'projects.create' ? true : false;
		$cancel = URL::previous();

        return View::make('projects.edit', compact('project', 'groups', 'count', 'create', 'cancel'));
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
        $project = $this->project->find($id);
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

        $filename = str_random(8) . '.' . $file->getClientOriginalExtension();
        $directory = Config::get('config.dataDir');

        try
        {
            Input::file('file')->move($directory, $filename);
			$user = Sentry::getUser();
            $this->import->create(['user_id' => $user->id,'project_id' => $id, 'file' => $filename]);
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
		$user = Sentry::getUser();
		$isSuperUser = $user->isSuperUser();
        $isOwner = ($user->id == $project->group->user_id || $isSuperUser) ? true : false;
        if ($isOwner)
        {
            $this->project->destroy($id);
            Session::flash('success', trans('projects.project_destroyed'));

            return Redirect::action('ProjectsController@all');
        }

        Session::flash('error', trans('projects.project_destroy_error'));
        return Redirect::action('ProjectsController@all');
	}

}

<?php
/**
 * ExpeditionsController.php
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
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Form\Expedition\ExpeditionForm;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;

class ExpeditionsController extends BaseController {

    /**
     * @var Biospex\Repo\Expedition\ExpeditionInterface
     */
    protected $expedition;

    /**
     * @var Biospex\Form\Expedition\ExpeditionForm
     */
    protected $expeditionForm;

    /**
     * @var Biospex\Repo\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var Biospex\Repo\Subject\SubjectInterface
     */
    protected $subject;

	/**
	 * Instantiate a new ExpeditionsController
	 *
	 * @param ExpeditionInterface $expedition
	 * @param ExpeditionForm $expeditionForm
	 * @param ProjectInterface $project
	 * @param SubjectInterface $subject
	 * @param WorkflowManagerInterface $workflowManager
	 */
    public function __construct(
        ExpeditionInterface $expedition,
        ExpeditionForm $expeditionForm,
        ProjectInterface $project,
        SubjectInterface $subject,
        WorkflowManagerInterface $workflowManager
    )
    {
        $this->expedition = $expedition;
        $this->expeditionForm = $expeditionForm;
        $this->project = $project;
        $this->subject = $subject;
        $this->workflowManager = $workflowManager;

        // Establish Filters
		$this->beforeFilter('auth');
		$this->beforeFilter('csrf', ['on' => 'post']);
		$this->beforeFilter('hasProjectAccess:expedition_view', ['only' => ['show', 'index']]);
		$this->beforeFilter('hasProjectAccess:expedition_edit', ['only' => ['edit', 'update']]);
		$this->beforeFilter('hasProjectAccess:expedition_delete', ['only' => ['destroy']]);
		$this->beforeFilter('hasProjectAccess:expedition_create', ['only' => ['create', 'store']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index ($id)
    {
		if ( ! Request::ajax())
			return Redirect::action('ProjectsController@show', [$id]);

		$project = $this->project->findWith($id, ['expeditions.actorsCompletedRelation']);

		return View::make('expeditions.index', compact('project'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function create ($id)
    {
		$project = $this->project->findWith($id, ['group']);
        $subjects = $this->subject->getUnassignedCount($id);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
		$cancel = URL::previous();

        return View::make('expeditions.create', compact('project', 'subjects', 'create', 'cancel'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store ()
    {
        // Form Processing
        $subjects = $this->subject->getSubjectIds(Input::get('project_id'), Input::get('subjects'));
		$input = array_merge(Input::all(), ['subject_ids' => $subjects]);

        $expedition = $this->expeditionForm->save($input);

        if($expedition)
        {
            Session::flash('success', trans('expeditions.expedition_created'));
            return Redirect::action('ExpeditionsController@show', [$expedition->project_id, $expedition->id]);
        }
        else
        {
            Session::flash('error', trans('expeditions.expedition_save_error'));
            return Redirect::action('ExpeditionsController@create')
                ->withInput()
                ->withErrors($this->expeditionForm->errors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function show ($projectId, $expeditionId)
    {
		$expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads', 'workflowManager']);

		return View::make('expeditions.show', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function duplicate ($projectId, $expeditionId)
    {
		$expedition = $this->expedition->findWith($expeditionId, ['project.group']);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
		$cancel = URL::previous();

		return View::make('expeditions.clone', compact('expedition', 'create', 'cancel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function edit ($projectId, $expeditionId)
    {
		$expedition = $this->expedition->findWith($expeditionId, ['project.group']);
        $subjects = count($expedition->subject);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
		$cancel = URL::previous();
		return View::make('expeditions.edit', compact('expedition', 'subjects', 'create', 'cancel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update ($projectId, $expeditionId)
    {
        // Form Processing
        $expedition = $this->expeditionForm->update(Input::all());

        if($expedition)
        {
            // Success!
            Session::flash('success', trans('expeditions.expedition_updated'));
            return Redirect::action('projects.expeditions.show', [$projectId, $expeditionId]);

        } else {
            Session::flash('error', trans('expeditions.expedition_save_error'));
            return Redirect::route('projects.expeditions.edit', [$projectId, $expeditionId])
                ->withInput()
                ->withErrors( $this->expeditionForm->errors() );
        }
    }

    /**
     * Start processing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        try
        {
			$expedition = $this->expedition->findWith($expeditionId, ['project.actors', 'workflowManager']);

			if ( ! is_null($expedition->workflowManager))
			{
                $workflowId = $expedition->workflowManager->id;
				$expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->queue = 1;
				$this->workflowManager->save($expedition->workflowManager);
			}
			else
			{
				$workflowManager = $this->workflowManager->create(['expedition_id' => $expeditionId, 'queue' => 1]);
				$workflowId = $workflowManager->id;
                $expedition->actors()->sync($expedition->project->actors);
			}

            Queue::push('Biospex\Services\Queue\WorkflowManagerService', ['id' => $workflowId], \Config::get('config.beanstalkd.workflowManager'));

            Session::flash('success', trans('expeditions.expedition_process_success'));
        }
        catch(Exception $e)
        {
            Session::flash('error', trans('expeditions.expedition_process_error'));
        }

        return Redirect::action('ExpeditionsController@show', [$projectId, $expeditionId]);
    }

	/**
	 * Stop a expedition process.
	 *
	 * @param $projectId
	 * @param $expeditionId
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function stop($projectId, $expeditionId)
	{
		$workflow = $this->workflowManager->findByExpeditionId($expeditionId);

		if (is_null($workflow))
		{
			Session::flash('error', trans('expeditions.process_no_exists'));
		}
		else
		{
			$workflow->stopped = 1;
            $workflow->queue = 0;
			$this->workflowManager->save($workflow);
			Session::flash('success', trans('expeditions.process_stopped'));
		}

		return Redirect::action('ExpeditionsController@show', [$projectId, $expeditionId]);

	}

    /**
     * Remove the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy ($projectId, $expeditionId)
    {
		$workflow = $this->workflowManager->findByExpeditionId($expeditionId);
		if ( ! is_null($workflow))
		{
			Session::flash('error', trans('expeditions.expedition_process_exists'));
			return Redirect::action('projects.expeditions.show', [$projectId, $expeditionId]);
		}
		else
		{
			try
			{
				$subjects = $this->subject->getSubjectIds($projectId, null, $expeditionId);
				$this->subject->detachSubjects($subjects, $expeditionId);
				$this->expedition->destroy($expeditionId);

				Session::flash('success', trans('expeditions.expedition_deleted'));
			}
			catch(Exception $e)
			{
				Session::flash('error', trans('expeditions.expedition_destroy_error'));
			}
		}

        return Redirect::action('projects.show', [$projectId]);
    }
}

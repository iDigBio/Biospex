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
use Biospex\Repo\Group\GroupInterface;
use Biospex\Repo\User\UserInterface;
use Biospex\Repo\Subject\SubjectInterface;

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
     * @var Biospex\Repo\Group\GroupInterface
     */
    protected $group;

    /**
     * @var Biospex\Repo\User\UserInterface
     */
    protected $user;

    /**
     * @var Biospex\Repo\Subject\SubjectInterface
     */
    protected $subject;

    /**
     * Instantiate a new ProjectsController
     */
    public function __construct(
        ExpeditionInterface $expedition,
        ExpeditionForm $expeditionForm,
        ProjectInterface $project,
        GroupInterface $group,
        UserInterface $user,
        SubjectInterface $subject
    )
    {
        $this->expedition = $expedition;
        $this->expeditionForm = $expeditionForm;
        $this->project = $project;
        $this->group = $group;
        $this->user = $user;
        $this->subject = $subject;

        // Establish Filters
        $this->beforeFilter('csrf', array('on' => 'post'));
        $this->beforeFilter('guest', array('only' => array('all')));
        $this->beforeFilter('hasProjectAccess:expedition_view', array('only' => array('show', 'index')));
        $this->beforeFilter('hasProjectAccess:expedition_edit', array('only' => array('edit', 'update')));
        $this->beforeFilter('hasProjectAccess:expedition_delete', array('only' => array('destroy')));
        $this->beforeFilter('hasProjectAccess:expedition_create', array('only' => array('create')));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index ($groupId, $projectId)
    {
        $expeditions = $this->expedition->findByProjectId($projectId);
        if (is_null($expeditions)) $expeditions = array();

        if (Request::ajax()) {
            return View::make('expeditions.indexajax', compact('groupId', 'projectId', 'expeditions'));
        }
        return View::make('expeditions.index', compact('groupId', 'projectId', 'expeditions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create ($groupId, $projectId)
    {
        $project = $this->project->find($projectId);
        $group = $project->group;
        $subjects = $this->subject->getUnassignedSubjectCount($projectId);

        return View::make('expeditions.create', compact('group', 'project', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store ($groupId, $projectId)
    {
        // Form Processing
        $subjects = $this->subject->getUnassignedSubjects(Input::only('project_id', 'subjects'));
        $input = array_merge(Input::all(), array('subject_ids' => $subjects));
        $expedition = $this->expeditionForm->save($input);

        if($expedition)
        {
            // Success!
            Session::flash('success', trans('expeditions.expedition-created'));
            return Redirect::action('ExpeditionsController@show', array($groupId, $projectId, $expedition->id));

        } else {
            Session::flash('error', trans('expeditions.expedition-save-error'));
            return Redirect::action('ExpeditionsController@create', $groupId)
                ->withInput()
                ->withErrors($this->expeditionForm->errors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show ($groupId, $projectId, $expeditionId)
    {
        $project = $this->project->find($projectId);
        $expedition = $this->expedition->find($expeditionId);

        return View::make('expeditions.show', compact('groupId', 'project', 'expedition'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $groupId
     * @param $projectId
     * @param $expeditionId
     */
    public function duplicate ($groupId, $projectId, $expeditionId)
    {
        $group = $this->group->find($groupId);
        $project = $this->project->find($projectId);
        $expedition = $this->expedition->find($expeditionId);
        $subjects = count($expedition->subject);
        return View::make('expeditions.clone', compact('group', 'project', 'expedition', 'subjects'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit ($groupId, $projectId, $expeditionId)
    {
        $group = $this->group->find($groupId);
        $project = $this->project->find($projectId);
        $expedition = $this->expedition->find($expeditionId);
        $subjects = count($expedition->subject);
        return View::make('expeditions.edit', compact('group', 'project', 'expedition', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update ($groupId, $projectId, $expeditionId)
    {
        // Form Processing
        $expedition = $this->expeditionForm->update(Input::all());

        if($expedition)
        {
            // Success!
            Session::flash('success', trans('expeditions.expedition-updated'));
            return Redirect::action('groups.projects.expeditions.show', array($groupId, $projectId, $expeditionId));

        } else {
            Session::flash('error', trans('expeditions.expedition-save-error'));
            return Redirect::route('groups.projects.expeditions.edit', array($groupId, $projectId, $expeditionId))
                ->withInput()
                ->withErrors( $this->expeditionForm->errors() );
        }
    }

    public function export($groupId, $projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['workflow']);
        $class ='Biospex\Services\Workflow\\' . $expedition->workflow->class_name;
        $workflow = $class::factory();
        $workflow->export($expeditionId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy ($groupId, $projectId, $expeditionId)
    {
        $result = $this->expedition->destroy($expeditionId);
        if($result)
        {
            Session::flash('success', trans('expeditions.expedition-deleted'));
        } else {
            Session::flash('error', trans('expeditions.expedition-delete-error'));
        }
        return Redirect::action('groups.projects.show', array($groupId, $projectId));
    }

}

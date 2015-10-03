<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\WorkflowManager;
use App\Services\Process\DarwinCore;

class ExpeditionsController extends Controller
{
    /**
     * @var Expedition
     */
    protected $expedition;

    /**
     * @var ExpeditionForm
     */
    protected $expeditionForm;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var App\Services\Process\DarwinCore
     */
    protected $process;

    /**
     * Instantiate a new ExpeditionsController
     *
     * @param Expedition $expedition
     * @param ExpeditionForm $expeditionForm
     * @param Project $project
     * @param Subject $subject
     * @param WorkflowManager $workflowManager
     * @param DarwinCore $process
     */
    public function __construct(
        Expedition $expedition,
        Project $project,
        Subject $subject,
        WorkflowManager $workflowManager,
        DarwinCore $process
    ) {
        $this->expedition = $expedition;
        $this->project = $project;
        $this->subject = $subject;
        $this->workflowManager = $workflowManager;
        $this->process = $process;
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function index($id)
    {
        if (! Request::ajax()) {
            return Redirect::action('ProjectsController@show', [$id]);
        }

        $project = $this->project->findWith($id, ['expeditions.actorsCompletedRelation']);

        return View::make('front.expeditions.index', compact('project'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function create($id)
    {
        $project = $this->project->findWith($id, ['group']);
        $subjects = $this->subject->getUnassignedCount($id);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
        $cancel = URL::previous();

        return View::make('front.expeditions.create', compact('project', 'subjects', 'create', 'cancel'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        // Form Processing
        $subjects = $this->subject->getSubjectIds(Input::get('project_id'), Input::get('subjects'));
        $input = array_merge(Input::all(), ['subject_ids' => $subjects]);

        $expedition = $this->expeditionForm->save($input);

        if ($expedition) {
            Session::flash('success', trans('expeditions.expedition_created'));
            return Redirect::action('ExpeditionsController@show', [$expedition->project_id, $expedition->id]);
        } else {
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
    public function show($projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads', 'workflowManager']);

        return view('front.expeditions.show', compact('expedition'));
    }

    /**
     * Clone an existing expedition
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function duplicate($projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['project.group']);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
        $cancel = URL::previous();

        return View::make('front.expeditions.clone', compact('expedition', 'create', 'cancel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function edit($projectId, $expeditionId)
    {
        $expedition = $this->expedition->findWith($expeditionId, ['project.group']);
        $subjects = count($expedition->subject);
        $create = Route::currentRouteName() == 'projects.expeditions.create' ? true : false;
        $cancel = URL::previous();
        return View::make('front.expeditions.edit', compact('expedition', 'subjects', 'create', 'cancel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($projectId, $expeditionId)
    {
        // Form Processing
        $expedition = $this->expeditionForm->update(Input::all());

        if ($expedition) {
            // Success!
            Session::flash('success', trans('expeditions.expedition_updated'));
            return Redirect::action('projects.expeditions.show', [$projectId, $expeditionId]);
        } else {
            Session::flash('error', trans('expeditions.expedition_save_error'));
            return Redirect::route('projects.expeditions.edit', [$projectId, $expeditionId])
                ->withInput()
                ->withErrors($this->expeditionForm->errors());
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
        try {
            $expedition = $this->expedition->findWith($expeditionId, ['project.actors', 'workflowManager']);

            if (! is_null($expedition->workflowManager)) {
                $workflowId = $expedition->workflowManager->id;
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->queue = 1;
                $this->workflowManager->save($expedition->workflowManager);
            } else {
                $workflowManager = $this->workflowManager->create(['expedition_id' => $expeditionId, 'queue' => 1]);
                $workflowId = $workflowManager->id;
                $expedition->actors()->sync($expedition->project->actors);
            }

            Queue::push('App\Services\Queue\WorkflowManagerService', ['id' => $workflowId], \Config::get('config.beanstalkd.workflow'));

            Session::flash('success', trans('expeditions.expedition_process_success'));
        } catch (Exception $e) {
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

        if (is_null($workflow)) {
            Session::flash('error', trans('expeditions.process_no_exists'));
        } else {
            $workflow->stopped = 1;
            $workflow->queue = 0;
            $this->workflowManager->save($workflow);
            Session::flash('success', trans('expeditions.process_stopped'));
        }

        return Redirect::action('ExpeditionsController@show', [$projectId, $expeditionId]);
    }

    public function ocr($projectId, $expeditionId)
    {
        try {
            $expedition = $this->expedition->findWith($expeditionId, ['subjects']);

            $data = [];
            $count = 0;
            $this->process->setProjectId($projectId);
            foreach ($expedition->subjects as $subject) {
                if (! empty($subject->ocr)) {
                    continue;
                }

                $this->process->buildOcrQueue($data, $subject);
                $count++;
            }

            if ($count > 0 && ! \Config::get('config.disableOcr')) {
                $id = $this->process->saveOcrQueue($data, $count);
                \Queue::push('App\Services\Queue\QueueFactory', ['id' => $id, 'class' => 'OcrProcessQueue'], Config::get('config.beanstalkd.ocr'));
            }

            Session::flash('success', trans('expeditions.ocr_process_success'));
        } catch (Exception $e) {
            Session::flash('error', trans('expeditions.ocr_process_error'));
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
    public function destroy($projectId, $expeditionId)
    {
        $workflow = $this->workflowManager->findByExpeditionId($expeditionId);
        if (! is_null($workflow)) {
            Session::flash('error', trans('expeditions.expedition_process_exists'));
            return Redirect::action('projects.expeditions.show', [$projectId, $expeditionId]);
        } else {
            try {
                $subjects = $this->subject->getSubjectIds($projectId, null, $expeditionId);
                $this->subject->detachSubjects($subjects, $expeditionId);
                $this->expedition->destroy($expeditionId);

                Session::flash('success', trans('expeditions.expedition_deleted'));
            } catch (Exception $e) {
                Session::flash('error', trans('expeditions.expedition_destroy_error'));
            }
        }

        return Redirect::action('projects.show', [$projectId]);
    }
}

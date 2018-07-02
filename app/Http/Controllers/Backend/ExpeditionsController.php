<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\ExpeditionFormRequest;
use App\Jobs\DeleteExpedition;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use App\Http\Controllers\Controller;

class ExpeditionsController extends Controller
{

    /**
     * @var User
     */
    public $userContract;

    /**
     * @var Expedition
     */
    public $expeditionContract;

    /**
     * ExpeditionsController constructor.
     *
     * @param User $userContract
     * @param Expedition $expeditionContract
     */
    public function __construct(
        User $userContract,
        Expedition $expeditionContract
    )
    {
        $this->userContract = $userContract;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($expeditionId = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $expeditions = $this->expeditionContract->all();

        $editExpedition = $expeditionId !== null ?
            $this->expeditionContract->findWith($expeditionId, ['project', 'nfnWorkflow']) : null;

        $variables = array_merge(compact('user', 'expeditions', 'editExpedition'));

        return view('backend.expeditions.index', $variables);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExpeditionFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request)
    {
        $expedition = $this->expeditionContract->create($request->all());

        if ($expedition)
        {
            Flash::success('The Expedition has been created successfully.');

            return redirect()->route('admin.expeditions.index');
        }

        Flash::error('The Expedition could not be created.');

        return redirect()->route('admin.expeditions.index')->withInput();
    }

    /**
     * Update project.
     *
     * @param ExpeditionFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ExpeditionFormRequest $request)
    {
        $expedition = $this->expeditionContract->update($request->all(), $request->input('id'));

        $expedition ?
            Flash::success('The Expedition has been updated.') :
            Flash::error('The Expedition failed to update.');

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Delete expedition.
     *
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($expeditionId)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['nfnWorkflow', 'downloads', 'workflowManager']);

        if (isset($expedition->workflowManager) || isset($expedition->nfnWorkflow))
        {
            Flash::error(trans('messages.expedition_process_exists'));

            return redirect()->route('admin.expeditions.index');
        }

        DeleteExpedition::dispatch($expedition);

        Flash::success(trans('messages.record_deleted'));

        return redirect()->route('admin.expeditions.index');
    }

}

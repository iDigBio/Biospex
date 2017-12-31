<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\ExpeditionFormRequest;
use App\Services\Model\ExpeditionService;
use App\Interfaces\Expedition;
use App\Interfaces\User;
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
     * @var ExpeditionService
     */
    private $expeditionService;

    /**
     * ExpeditionsController constructor.
     *
     * @param User $userContract
     * @param ExpeditionService $expeditionService
     * @param Expedition $expeditionContract
     */
    public function __construct(
        User $userContract,
        ExpeditionService $expeditionService,
        Expedition $expeditionContract
    )
    {
        $this->userContract = $userContract;
        $this->expeditionContract = $expeditionContract;
        $this->expeditionService = $expeditionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($id = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $expeditions = $this->expeditionService->getAllExpeditions();
        $trashed = $this->expeditionService->getOnlyTrashedExpeditions();

        $editExpedition = $id !== null ? $this->expeditionService->findExpeditionWith($id, ['project', 'nfnWorkflow']) : null;

        $variables = array_merge(compact('user', 'expeditions', 'trashed', 'editExpedition'));

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
        $expedition = $this->expeditionService->create($request->all());

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
        $expedition = $this->expeditionService->updateAdminExpedition($request->all(), $request->input('id'));

        $expedition ?
            Flash::success('The Expedition has been updated.') :
            Flash::error('The Expedition failed to update.');

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Delete expedition.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $this->expeditionService->deleteExpedition($id);

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Destroy expedition.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->expeditionService->destroyExpedition($id);

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Restore expedition.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $this->expeditionService->restoreExpedition($id);

        return redirect()->route('admin.expeditions.index');
    }
}

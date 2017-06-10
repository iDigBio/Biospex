<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\ExpeditionFormRequest;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\User;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExpeditionsController extends Controller
{

    /**
     * @var User
     */
    public $user;

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * @var Request
     */
    public $request;

    /**
     * ExpeditionsController constructor.
     *
     * @param User $user
     * @param ExpeditionContract $expeditionContract
     * @param Request $request
     */
    public function __construct(
        User $user,
        ExpeditionContract $expeditionContract,
        Request $request)
    {
        $this->user = $user;
        $this->expeditionContract = $expeditionContract;
        $this->request = $request;
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($id = null)
    {
        $user = $this->user->skipCache()->with(['profile'])->find($this->request->user()->id);
        $expeditions = $this->expeditionContract->setCacheLifetime(0)->findAll();
        $trashed = $this->expeditionContract->setCacheLifetime(0)->onlyTrashed();

        $editExpedition = $id !== null ? $this->expeditionContract->with(['project', 'nfnWorkflow'])->find($id) : null;

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
        $expedition = $this->expeditionContract->createExpedition($request->all());

        if ($expedition)
        {
            Toastr::success('The Expedition has been created successfully.', 'Expedition Create');
            return redirect()->route('admin.expeditions.index');
        }

        Toastr::error('The Expedition could not be created.', 'Expedition Create');

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
        $expedition = $this->expeditionContract->updateExpedition($request->input('id'), $request->all());

        $expedition ?
            Toastr::success('The Expedition has been updated.', 'Expedition Update') :
            Toastr::error('The Expedition failed to update.', 'Expedition Update');

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Delete expedition.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $service->deleteExpedition($id) ?
            Toastr::success('The Expedition has been deleted.', 'Expedition Delete') :
            Toastr::error('The Expedition could not be deleted.', 'Expedition Delete');

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Destroy expedition.
     *
     * @param ModelDestroyService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $service->destroyExpedition($id) ?
            Toastr::success('The Expedition has been forcefully deleted.', 'Expedition Destroy') :
            Toastr::error('The Expedition could not be forcefully deleted.', 'Expedition Destroy');

        return redirect()->route('admin.expeditions.index');
    }

    /**
     * Restore expedition.
     *
     * @param ModelRestoreService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $id)
    {
        $service->restoreExpedition($id) ?
            Toastr::success('The Expedition has been restored successfully.', 'Expedition Restore') :
            Toastr::error('Expedition could not be restored.', 'Expedition Restore');

        return redirect()->route('admin.expeditions.index');
    }
}

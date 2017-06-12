<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActorFormRequest;
use App\Repositories\Contracts\ActorContract;
use App\Services\Actor\ActorAdminService;
use Illuminate\Http\Request;

class ActorsController extends Controller
{
    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * @var ActorAdminService
     */
    private $actorAdminService;

    /**
     * ActorsController constructor.
     *
     * @param ActorAdminService $actorAdminService
     * @param ActorContract $actorContract
     */
    public function __construct(
        ActorAdminService $actorAdminService,
        ActorContract $actorContract
    )
    {
        $this->actorContract = $actorContract;
        $this->actorAdminService = $actorAdminService;
    }

    /**
     * Show Faq list by category.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return $this->actorAdminService->showIndex($request);
    }

    /**
     * Create form.
     *
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        return $this->actorAdminService->showCreateForm($request);
    }

    /**
     * Create Actor.
     *
     * @param ActorFormRequest $request
     * @return mixed
     */
    public function store(ActorFormRequest $request)
    {
        $this->actorAdminService->createActor($request) ?
            Toastr::success('Actor has been created successfully.', 'Actor Create') :
            Toastr::error('Actor could not be saved.', 'Actor Create');

        return redirect()->route('admin.actors.index');
    }

    /**
     * Edit Actor.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        return $this->actorAdminService->editActor($request, $id);
    }

    /**
     * Update Actor.
     *
     * @param ActorFormRequest $request
     * @param $id
     * @return mixed
     */
    public function update(ActorFormRequest $request, $id)
    {
        return $this->actorAdminService->updateActor($request, $id);
    }

    /**
     * Delete actor.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $result = $this->actorContract->delete($id);

        $result ? Toastr::success('The actor has been deleted.', 'Actor Delete')
                : Toastr::error('Actor could not be deleted.', 'Actor Delete');

        return redirect()->route('admin.actors.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $result = $this->actorContract->forceDelete($id);

        $result ? Toastr::success('Actor has been forcefully deleted.', 'Actor Destroy')
            : Toastr::error('Actor could not be forcefully deleted.', 'Actor Destroy');
        
        return redirect()->route('admin.actors.index');
    }
}

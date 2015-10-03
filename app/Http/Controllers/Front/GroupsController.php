<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Common\GroupService;
use App\Http\Requests\GroupFormRequest;

class GroupsController extends Controller
{
    /**
     * @var GroupService
     */
    private $service;

    /**
     * Instantiate a new GroupsController
     *
     * @param GroupService $service
     * @param Sentry $sentry
     * @param User $user
     * @param Dispatcher $events
     * @param Group $group
     * @param Permission $permission
     * @internal param GroupForm $groupForm
     */
    public function __construct(GroupService $service)
    {
        $this->service = $service;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $vars = $this->service->index();

        return view('front.groups.index', $vars);
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vars = $this->service->showForm();

        return view('front.groups.create', $vars);
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request)
    {
        if ($this->service->store($request)) {
            return redirect()->route('groups.index');
        }

        return redirect()->route('groups.create');
    }

    /**
     * Show group page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $vars = $this->service->show();

        return view('front.groups.show', $vars);
    }

    /**
     * Show group edit form.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $vars = $this->service->edit();

        return view('front.groups.edit', $vars);
    }

    /**
     * Update group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request)
    {
        if ($this->service->update($request)) {
            return redirect()->route('groups.index');
        }

        return redirect()->route('groups.edit', $request->get('id'));
    }

    /**
     * Remove group.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        if ($this->service->destroy()) {
            return redirect()->route('groups.index');
        }

        return redirect()->route('groups.index');
    }
}

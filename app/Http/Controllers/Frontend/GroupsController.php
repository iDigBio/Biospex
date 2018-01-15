<?php

namespace App\Http\Controllers\Frontend;

use Auth;
use App\Services\Model\GroupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Repositories\Interfaces\User;

class GroupsController extends Controller
{

    /**
     * @var
     */
    public $groupService;

    /**
     * @var User
     */
    public $userContract;

    /**
     * GroupsController constructor.
     *
     * @param GroupService $groupService
     * @param User $userContract
     */
    public function __construct(GroupService $groupService, User $userContract)
    {
        $this->groupService = $groupService;
        $this->userContract = $userContract;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['groups']);
        $trashed = $this->userContract->findWith(request()->user()->id, ['groupsTrashed']);

        return view('frontend.groups.index', compact('user', 'trashed'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();

        return view('frontend.groups.create', compact('user'));
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request)
    {
        if ($this->groupService->createGroup(Auth::user(), $request->get('title')))
        {
            return redirect()->route('web.groups.index');
        }

        return redirect()->route('web.groups.create');
    }

    /**
     * Show group page.
     *
     * @param $groupId
     * @return \Illuminate\View\View
     */
    public function show($groupId)
    {
        $with = [
            'projects',
            'owner.profile',
            'users.profile'
        ];

        $group = $this->groupService->findGroupWith($groupId, $with);

        if ( ! $this->checkPermissions('read', $group))
        {
            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.show', compact('group'));
    }

    /**
     * Show group edit form.
     *
     * @param $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($groupId)
    {
        $group = $this->groupService->findGroupWith($groupId, ['owner']);

        if ( ! $this->checkPermissions('update', $group))
        {
            return redirect()->route('web.groups.index');
        }

        $users = $this->groupService->getGroupUsersSelect($group->id);

        return view('frontend.groups.edit', compact('group', 'users'));
    }

    /**
     * Update group.
     *
     * @param GroupFormRequest $request
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request, $groupId)
    {
        $group = $this->groupService->findGroup($groupId);

        if ($this->checkPermissions('update', $group))
        {
            return redirect()->route('web.groups.index');
        }

        $this->groupService->updateGroup($request->all(), $group->id);

        return redirect()->route('web.groups.index');
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($groupId)
    {
        $group = $this->groupService->findGroupWith($groupId, ['projects.nfnWorkflows']);

        if ( ! $this->checkPermissions('delete', $group))
        {
            return redirect()->route('web.groups.index');
        }

        $this->groupService->deleteGroup($group);

        return redirect()->route('web.groups.index');
    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($groupId)
    {
        $group = $this->groupService->findTrashed($groupId);

        if ( ! $this->checkPermissions('delete', $group))
        {
            return redirect()->route('web.groups.index');
        }

        $this->groupService->destroyGroup($group);

        return redirect()->route('web.groups.index');
    }

    /**
     * Restore group.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($groupId)
    {
        $group = $this->groupService->findTrashed($groupId);

        if ( ! $this->checkPermissions('delete', $group))
        {
            return redirect()->route('web.groups.index');
        }

        $this->groupService->restoreGroup($group);

        return redirect()->route('web.groups.index');
    }
}

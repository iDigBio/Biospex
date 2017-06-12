<?php

namespace App\Http\Controllers\Frontend;

use App\Services\Model\GroupService;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Repositories\Contracts\UserContract;

class GroupsController extends Controller
{

    /**
     * @var
     */
    public $groupService;

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * GroupsController constructor.
     *
     * @param GroupService $groupService
     * @param UserContract $userContract
     */
    public function __construct(GroupService $groupService, UserContract $userContract)
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
        $user = $this->userContract->with('groups')->find(request()->user()->id);
        $trashed = $this->userContract->with('trashedGroups')->find(request()->user()->id);

        return view('frontend.groups.index', compact('user', 'trashed'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = request()->user();

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
        $user = request()->user();

        $group = $this->groupService->groupContract->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);

            event('group.saved');

            session_flash_push('success', trans('groups.created'));

            return redirect()->route('web.groups.index');
        }

        session_flash_push('warning', trans('groups.loginreq'));

        return redirect()->route('web.groups.create');
    }

    /**
     * Show group page.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = request()->user();
        $with = [
            'projects',
            'owner.profile',
            'users.profile'
        ];
        $group = $this->groupService->groupContract->with($with)->find($id);

        if ($user->cannot('show', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        return view('frontend.groups.show', compact('group'));

    }

    /**
     * Show group edit form.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = request()->user();
        $group = $this->groupService->groupContract->with('owner')->find($id);
        $users = $this->groupService->getGroupUsersSelect($group->id);

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

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
        $user = request()->user();

        $group = $this->groupService->groupContract->find($groupId);

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        $this->groupService->groupContract->update($group->id, $request->all());

        event('group.saved');

        session_flash_push('success', trans('groups.updated'));

        return redirect()->route('web.groups.index');
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $group = $service->groupService->groupContract->find($id);

        if (request()->user()->cannot('delete', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.groups.index');
        }

        $service->deleteGroup($group->id) ?
            session_flash_push('success', trans('groups.group_deleted')) :
            session_flash_push('error', trans('groups.group_deleted_failed'));

        return redirect()->route('web.groups.index');
    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param ModelDestroyService $service
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $groupId)
    {
        $group = $this->groupService->groupContract->onlyTrashed($groupId);

        if ( ! $this->checkPermissions(request()->user(), [$group], 'delete'))
        {
            return redirect()->route('web.groups.index');
        }

        $service->destroyGroup($group->id) ?
            session_flash_push('success', trans('groups.group_destroyed')) :
            session_flash_push('error', trans('groups.group_destroyed_failed'));

        return redirect()->route('web.groups.index');

    }

    /**
     * Restore group.
     *
     * @param ModelRestoreService $service
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $groupId)
    {
        $service->restoreGroup($groupId) ?
            session_flash_push('success', trans('projects.group_restored')) :
            session_flash_push('error', trans('projects.group_restored_failed'));

        return redirect()->route('web.groups.index');
    }
}

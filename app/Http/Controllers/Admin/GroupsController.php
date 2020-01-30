<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\DeleteGroup;
use Auth;
use App\Facades\FlashHelper;
use App\Repositories\Interfaces\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Repositories\Interfaces\User;

class GroupsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * GroupsController constructor.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     */
    public function __construct(Group $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $groups = $this->groupContract->getGroupsByUserId(Auth::id());

        return view('admin.group.index', compact('groups'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.group.create');
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @param \App\Repositories\Interfaces\User $userContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request, User $userContract)
    {
        $user = Auth::user();
        $group = $this->groupContract->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);
            $admin = $userContract->find(1);
            $admin->assignGroup($group);

            event('group.saved');

            FlashHelper::success(trans('messages.record_created'));

            return redirect()->route('admin.groups.index');
        }

        FlashHelper::warning(trans('messages.loginreq'));

        return redirect()->back();
    }

    /**
     * how group page.
     *
     * @param $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function show($groupId)
    {
        $group = $this->groupContract->getGroupShow($groupId);

        if (! $this->checkPermissions('read', $group)) {
            return redirect()->back();
        }

        return view('admin.group.show', compact('group'));
    }

    /**
     * Show group edit form.
     *
     * @param $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['owner', 'users.profile']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->back();
        }

        $users = $group->users->mapWithKeys(function ($user) {
            return [$user->id => $user->profile->full_name];
        });

        return view('admin.group.edit', compact('group', 'users'));
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
        $group = $this->groupContract->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->back();
        }

        $this->groupContract->update($request->all(), $groupId) ? FlashHelper::success(trans('messages.record_updated')) : FlashHelper::error('messages.record_updated_error');

        return redirect()->route('admin.groups.show', [$groupId]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['projects.panoptesProject', 'projects.workflowManagers']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->back();
        }

        try {
            foreach ($group->projects as $project) {
                if ($project->panoptesProjects->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                    FlashHelper::error(trans('messages.expedition_process_exists'));

                    return redirect()->route('admin.groups.index');
                }
            }

            DeleteGroup::dispatch($group);

            event('group.deleted', $group->id);

            FlashHelper::success(trans('messages.record_deleted'));

            return redirect()->route('admin.groups.index');
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.record_delete_error'));

            return redirect()->route('admin.groups.index');
        }
    }

    /**
     * Delete user from group.
     *
     * @param \App\Repositories\Interfaces\User $userContract
     * @param $groupId
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(User $userContract, $groupId, $userId)
    {
        $group = $this->groupContract->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->route('admin.groups.index');
        }

        try {
            if ($group->user_id === (int) $userId) {
                FlashHelper::error(trans('messages.group_user_deleted_owner'));

                return redirect()->route('admin.groups.show', [$groupId]);
            }

            $user = $userContract->find($userId);
            $user->detachGroup($group->id);

            FlashHelper::success(trans('messages.group_user_deleted'));

            return redirect()->route('admin.groups.show', [$groupId]);
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.group_user_deleted_error'));

            return redirect()->route('admin.groups.show', [$groupId]);
        }
    }
}

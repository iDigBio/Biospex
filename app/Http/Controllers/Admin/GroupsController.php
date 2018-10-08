<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\DeleteGroup;
use Auth;
use App\Facades\Flash;
use App\Repositories\Interfaces\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Repositories\Interfaces\User;
use Illuminate\Foundation\Bus\Dispatchable;

class GroupsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * @var \App\Repositories\Interfaces\User
     */
    private $userContract;

    /**
     * GroupsController constructor.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Repositories\Interfaces\User $userContract
     */
    public function __construct(Group $groupContract, User $userContract)
    {
        $this->groupContract = $groupContract;
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

        return view('frontend.groups.index', compact('user'));
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
        $user = Auth::user();
        $group = $this->groupContract->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);

            event('group.saved');

            Flash::success(trans('messages.record_created'));

            return redirect()->route('webauth.groups.index');
        }

        Flash::warning(trans('messages.loginreq'));

        return redirect()->route('webauth.groups.create');
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
            'users.profile',
        ];

        $group = $this->groupContract->findWith($groupId, $with);

        if (! $this->checkPermissions('read', $group)) {
            return redirect()->route('admin.groups.index');
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
        $group = $this->groupContract->findWith($groupId, ['owner', 'users.profile']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->route('webauth.groups.index');
        }

        $users = $group->users->mapWithKeys(function ($user) {
            return [$user->id => $user->profile->full_name];
        });

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
        $group = $this->groupContract->find($groupId);

        if ($this->checkPermissions('isOwner', $group)) {
            return redirect()->route('webauth.groups.index');
        }

        $this->groupContract->update($request->all(), $groupId) ? Flash::success(trans('messages.record_updated')) : Flash::error('messages.record_updated_error');

        return redirect()->route('webauth.groups.index');
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($groupId)
    {
        $group = $this->groupContract->findWith($groupId, ['projects.nfnWorkflows', 'projects.workflowManagers']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->route('webauth.groups.index');
        }

        try {
            foreach ($group->projects as $project) {
                if ($project->nfnWorkflows->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                    Flash::error(trans('messages.expedition_process_exists'));

                    return redirect()->route('webauth.groups.index');
                }
            }

            DeleteGroup::dispatch($group);

            event('group.deleted', $group->id);

            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('webauth.groups.index');
        } catch (\Exception $e) {
            Flash::error(trans('messages.record_delete_error'));

            return redirect()->route('webauth.groups.index');
        }
    }

    /**
     * Delete user from group.
     *
     * @param $groupId
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($groupId, $userId)
    {
        $group = $this->groupContract->find($groupId);

        if ( ! $this->checkPermissions('isOwner', $group)) {
            return redirect()->route('webauth.groups.index');
        }

        try {
            if ($group->user_id === $userId) {
                Flash::error(trans('messages.group_user_deleted_owner'));
                return redirect()->route('webauth.groups.show', [$group->id]);
            }

            $user = $this->userContract->find($userId);
            $user->detachGroup($group->id);

            Flash::success(trans('messages.group_user_deleted'));

            return redirect()->route('webauth.groups.show', [$group->id]);
        } catch (\Exception $e) {
            Flash::error(trans('messages.group_user_deleted_error'));
            return redirect()->route('webauth.groups.show', [$group->id]);
        }
    }
}

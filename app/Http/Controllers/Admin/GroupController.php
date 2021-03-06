<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Jobs\DeleteGroup;
use Auth;
use Flash;
use App\Services\Model\GroupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Services\Model\UserService;
use Exception;

/**
 * Class GroupController
 *
 * @package App\Http\Controllers\Admin
 */
class GroupController extends Controller
{
    /**
     * @var \App\Services\Model\GroupService
     */
    private $groupService;

    /**
     * GroupController constructor.
     *
     * @param \App\Services\Model\GroupService $groupService
     */
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $groups = $this->groupService->getGroupsByUserId(Auth::id());

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
     * @param \App\Services\Model\UserService $userService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request, UserService $userService)
    {
        $user = Auth::user();
        $group = $this->groupService->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);
            $admin = $userService->find(1);
            $admin->assignGroup($group);

            event('group.saved');

            Flash::success(t('Record was created successfully.'));

            return redirect()->route('admin.groups.index');
        }

        Flash::warning(t('Login field required'));

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
        $group = $this->groupService->getGroupShow($groupId);

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
        $group = $this->groupService->findWith($groupId, ['owner', 'users.profile']);

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
        $group = $this->groupService->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->back();
        }

        $this->groupService->update($request->all(), $groupId) ? Flash::success(t('Record was updated successfully.')) : Flash::error(t('Error while updating record.'));

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
        $group = $this->groupService->findWith($groupId, ['projects.panoptesProjects', 'projects.workflowManagers']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->back();
        }

        try {
            foreach ($group->projects as $project) {
                if ($project->panoptesProjects->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                    Flash::error(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                    return redirect()->route('admin.groups.index');
                }
            }

            DeleteGroup::dispatch($group);

            event('group.deleted', $group->id);

            Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return redirect()->route('admin.groups.index');
        } catch (Exception $e) {
            Flash::error(t('An error occurred when deleting record.'));

            return redirect()->route('admin.groups.index');
        }
    }

    /**
     * Delete user from group.
     *
     * @param \App\Services\Model\UserService $userService
     * @param $groupId
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(UserService $userService, $groupId, $userId)
    {
        $group = $this->groupService->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return redirect()->route('admin.groups.index');
        }

        try {
            if ($group->user_id === (int) $userId) {
                Flash::error(t('You cannot delete the owner until another owner is selected.'));

                return redirect()->route('admin.groups.show', [$groupId]);
            }

            $user = $userService->find($userId);
            $user->detachGroup($group->id);

            Flash::success(t('User was removed from the group'));

            return redirect()->route('admin.groups.show', [$groupId]);
        } catch (Exception $e) {
            Flash::error(t('There was an error removing user from the group'));

            return redirect()->route('admin.groups.show', [$groupId]);
        }
    }
}

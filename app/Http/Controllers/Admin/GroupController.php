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

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupFormRequest;
use App\Jobs\DeleteGroupJob;
use App\Models\Group;
use App\Services\Group\GroupService;
use App\Services\Permission\CheckPermission;
use Auth;
use Redirect;
use Throwable;
use View;

/**
 * Class GroupController
 */
class GroupController extends Controller
{
    /**
     * GroupController constructor.
     */
    public function __construct(protected GroupService $groupService) {}

    /**
     * Display groups.
     */
    public function index(): mixed
    {
        $groups = $this->groupService->getAdminIndex();

        return View::make('admin.group.index', compact('groups'));
    }

    /**
     * Show create group form.
     */
    public function create(): mixed
    {
        return View::make('admin.group.create');
    }

    /**
     * Store a newly created group.
     */
    public function store(GroupFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->groupService->storeGroup(Auth::user(), $request->all());

            return Redirect::route('admin.groups.index')->with('success', t('Group successfully created.'));
        } catch (Throwable $throwable) {
            return Redirect::back()->with('danger', t('Failed to create Group.'));
        }
    }

    /**
     * Show a group.
     */
    public function show(Group $group): mixed
    {
        if (! CheckPermission::handle('read', $group)) {
            return Redirect::back();
        }

        $this->groupService->showGroup($group);

        return View::make('admin.group.show', compact('group'));
    }

    /**
     * Show group edit form.
     */
    public function edit(Group $group): mixed
    {
        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        $users = $this->groupService->editGroup($group);

        return View::make('admin.group.edit', compact('group', 'users'));
    }

    /**
     * Update group.
     */
    public function update(Group $group, GroupFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        return $group->fill($request->all())->save() ?
            Redirect::route('admin.groups.show', [$group])->with('success', t('Record was updated successfully.')) :
            Redirect::route('admin.groups.show', [$group])->with('danger', t('Error while updating record.'));
    }

    /**
     * Delete group.
     */
    public function destroy(Group $group): \Illuminate\Http\RedirectResponse
    {
        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        try {
            $this->groupService->deleteGroup($group);

            DeleteGroupJob::dispatch($group);

            event('group.deleted', $group->id);

            return Redirect::route('admin.groups.index')
                ->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

        } catch (Throwable $throwable) {

            return Redirect::route('admin.groups.index')->with('danger', t('An error occurred when deleting record.'));
        }
    }
}

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
use App\Services\Group\GroupService;

/**
 * Class GroupController
 *
 * @package App\Http\Controllers\Admin
 */
class GroupController extends Controller
{
    /**
     * @var \App\Services\Group\GroupService
     */
    private GroupService $groupService;

    /**
     * GroupController constructor.
     *
     * @param \App\Services\Group\GroupService $groupService
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
    public function index(): \Illuminate\View\View
    {
        return $this->groupService->getAdminIndex();
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create(): \Illuminate\View\View
    {
        return \View::make('admin.group.create');
    }

    /**
     * Store a newly created group.
     *
     * @param \App\Http\Requests\GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function store(GroupFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        return $this->groupService->storeGroup();
    }

    /**
     * Show a group.
     *
     * @param int $groupId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(int $groupId)
    {
        return $this->groupService->showGroup($groupId);
    }

    /**
     * Show group edit form.
     *
     * @param int $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit(int $groupId)
    {
        return $this->groupService->editGroup($groupId);
    }

    /**
     * Update group.
     *
     * @param \App\Http\Requests\GroupFormRequest $request
     * @param int $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request, int $groupId): \Illuminate\Http\RedirectResponse
    {
        return $this->groupService->updateGroup($groupId);
    }

    /**
     * Delete group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $groupId): \Illuminate\Http\RedirectResponse
    {
        return $this->groupService->deleteGroup($groupId);
    }

    /**
     * Delete user from group.
     *
     * @param int $groupId
     * @param int $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGroupUser(int $groupId, int $userId)
    {
        return $this->groupService->deleteUserFromGroup($groupId, $userId);
    }

    /**
     * Delete geolocate form.
     *
     * @param int $groupId
     * @param int $formId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGeoLocateForm(int $groupId, int $formId)
    {
        return $this->groupService->deleteGeoLocateForm($groupId, $formId);
    }
}

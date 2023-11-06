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
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use App\Services\Actors\GeoLocate\GeoLocateExportForm;
use Auth;
use Exception;

/**
 * Class GroupController
 *
 * @package App\Http\Controllers\Admin
 */
class GroupController extends Controller
{
    /**
     * @var \App\Repositories\GroupRepository
     */
    private $groupRepo;

    /**
     * GroupController constructor.
     *
     * @param \App\Repositories\GroupRepository $groupRepo
     */
    public function __construct(GroupRepository $groupRepo)
    {
        $this->groupRepo = $groupRepo;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $groups = $this->groupRepo->getGroupsByUserId(Auth::id());

        return \View::make('admin.group.index', compact('groups'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return \View::make('admin.group.create');
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @param \App\Repositories\UserRepository $userRepo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request, UserRepository $userRepo)
    {
        $user = Auth::user();
        $group = $this->groupRepo->create(['user_id' => $user->id, 'title' => $request->get('title')]);

        if ($group) {
            $user->assignGroup($group);
            $admin = $userRepo->find(1);
            $admin->assignGroup($group);

            event('group.saved');

            \Flash::success(t('Record was created successfully.'));

            return \Redirect::route('admin.groups.index');
        }

        \Flash::warning(t('Login field required'));

        return \Response::back();
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
        $group = $this->groupRepo->getGroupShow($groupId);

        if (! $this->checkPermissions('read', $group)) {
            return \Response::back();
        }

        return \View::make('admin.group.show', compact('group'));
    }

    /**
     * Show group edit form.
     *
     * @param $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($groupId)
    {
        $group = $this->groupRepo->findWith($groupId, ['owner', 'users.profile']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return \Response::back();
        }

        $users = $group->users->mapWithKeys(function ($user) {
            return [$user->id => $user->profile->full_name];
        });

        return \View::make('admin.group.edit', compact('group', 'users'));
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
        $group = $this->groupRepo->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return \Response::back();
        }

        $this->groupRepo->update($request->all(), $groupId) ? \Flash::success(t('Record was updated successfully.')) : \Flash::error(t('Error while updating record.'));

        return \Redirect::route('admin.groups.show', [$groupId]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($groupId)
    {
        $group = $this->groupRepo->findWith($groupId, ['projects.panoptesProjects', 'projects.workflowManagers']);

        if (! $this->checkPermissions('isOwner', $group)) {
            return \Response::back();
        }

        try {
            foreach ($group->projects as $project) {
                if ($project->panoptesProjects->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                    \Flash::error(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                    return \Redirect::route('admin.groups.index');
                }
            }

            DeleteGroupJob::dispatch($group);

            event('group.deleted', $group->id);

            \Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return \Redirect::route('admin.groups.index');
        } catch (Exception $e) {
            \Flash::error(t('An error occurred when deleting record.'));

            return \Redirect::route('admin.groups.index');
        }
    }

    /**
     * Delete user from group.
     *
     * @param \App\Repositories\UserRepository $userRepo
     * @param $groupId
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(UserRepository $userRepo, $groupId, $userId)
    {
        $group = $this->groupRepo->find($groupId);

        if (! $this->checkPermissions('isOwner', $group)) {
            return \Redirect::route('admin.groups.index');
        }

        try {
            if ($group->user_id === (int) $userId) {
                \Flash::error(t('You cannot delete the owner until another owner is selected.'));

                return \Redirect::route('admin.groups.show', [$groupId]);
            }

            $user = $userRepo->find($userId);
            $user->detachGroup($group->id);

            \Flash::success(t('User was removed from the group'));

            return \Redirect::route('admin.groups.show', [$groupId]);
        } catch (Exception $e) {
            \Flash::error(t('There was an error removing user from the group'));

            return \Redirect::route('admin.groups.show', [$groupId]);
        }
    }

    /**
     * Delete geolocate form.
     *
     * @param \App\Services\Actors\GeoLocate\GeoLocateExportForm $geoLocateExportForm
     * @param int $groupId
     * @param int $formId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteForm(GeoLocateExportForm $geoLocateExportForm, int $groupId, int $formId): \Illuminate\Http\RedirectResponse
    {
        try {
            $group = $this->groupRepo->findWith($groupId);

            if (! $this->checkPermissions('isOwner', $group)) {
                return \Redirect::back();
            }

            $geoLocateForm = $geoLocateExportForm->findGeoLocateFormByIdWithExpeditionCount($formId);

            if ($geoLocateForm->expeditions_count > 0) {
                \Flash::error(t('GeoLocateExport Form cannot be deleted while still being used by Expeditions.'));

                return \Redirect::route('admin.groups.show', [$groupId]);
            }

            $geoLocateForm->delete();

            \Flash::success(t('GeoLocateExport Form was deleted.'));

            return \Redirect::route('admin.groups.show', [$groupId]);
        } catch (Exception $e) {
            \Flash::error(t('There was an error deleteing the GeoLocateExport Form.'));

            return \Redirect::route('admin.groups.show', [$groupId]);
        }
    }
}

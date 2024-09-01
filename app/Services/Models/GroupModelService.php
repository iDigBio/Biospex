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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Jobs\DeleteGroupJob;
use App\Models\GeoLocateForm;
use App\Models\Group;
use App\Models\User;
use App\Services\Permission\CheckPermission;
use Exception;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

readonly class GroupModelService
{
    public function __construct(private Group $model)
    {
    }

    /**
     * Get group ids for user session.
     *
     * @param int $id
     * @param array $relations
     * @return \App\Models\Group|null
     */
    public function findWithRelations(int $id, array $relations = []): \App\Models\Group|null
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function getAdminIndex(): \Illuminate\View\View
    {
        $groups = $this->model->withCount(['projects', 'expeditions', 'users'])->whereHas('users', function ($q) {
                $q->where('user_id', Auth::id());
            })->get();

        return View::make('admin.group.index', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function storeGroup(): \Illuminate\Http\RedirectResponse
    {
        $group = $this->model->create(['user_id' => Auth::id(), 'title' => request()->get('title')]);

        if ($group) {
            Auth::user()->assignGroup($group);
            $admin = User::find(1);
            $admin->assignGroup($group);

            event('group.saved');

            return Redirect::route('admin.groups.index')->with('success', t('Group successfully created.'));
        }

        return Redirect::back()->with('error', t('Failed to create Group.'));
    }

    /**
     * Show group.
     *
     * @param int $groupId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showGroup(int $groupId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $group = $this->model->with([
            'projects',
            'expeditions',
            'geoLocateForms.expeditions:id,project_id,geo_locate_form_id',
            'owner.profile',
            'users.profile',
        ])->withCount('expeditions')->find($groupId);

        if (! CheckPermission::handle('read', $group)) {
            return Redirect::back();
        }

        return View::make('admin.group.show', compact('group'));
    }

    /**
     * Show group edit form.
     *
     * @param int $groupId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editGroup(int $groupId
    ): \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse {
        $group = $this->model->with(['owner', 'users.profile'])->find($groupId);

        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        $users = $group->users->mapWithKeys(function ($user) {
            return [$user->id => $user->profile->full_name];
        });

        return View::make('admin.group.edit', compact('group', 'users'));
    }

    public function updateGroup(int $groupId): \Illuminate\Http\RedirectResponse
    {
        $group = $this->model->find($groupId);

        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        return $group->fill(request()->all())->save() ? Redirect::route('admin.groups.show', [$group->id])->with('success', t('Record was updated successfully.')) : Redirect::route('admin.groups.show', [$group->id])->with('error', t('Error while updating record.'));
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param int $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGroup(int $groupId): \Illuminate\Http\RedirectResponse
    {
        $group = $this->model->withCount(['panoptesProjects'])->with([
            'projects' => function ($q) {
                $q->withCount('workflowManagers');
            },
        ])->find($groupId);

        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::back();
        }

        try {
            if ($group->panoptes_projects_count > 0 || $group->projects->sum('id') > 0) {

                return Redirect::route('admin.groups.index')->with('error', t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));
            }

            DeleteGroupJob::dispatch($group);

            event('group.deleted', $group->id);

            return Redirect::route('admin.groups.index')->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        } catch (Exception $e) {

            return Redirect::route('admin.groups.index')->with('error', t('An error occurred when deleting record.'));
        }
    }

    /**
     * Delete user from group.
     *
     * @param int $groupId
     * @param int $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUserFromGroup(int $groupId, int $userId): \Illuminate\Http\RedirectResponse
    {
        $group = $this->model->find($groupId);

        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::route('admin.groups.index');
        }

        try {
            if ($group->user_id === $userId) {

                return Redirect::route('admin.groups.show', [$groupId])->with('error', t('You cannot delete the owner until another owner is selected.'));
            }

            $user = User::find($userId);
            $user->detachGroup($group->id);

            return Redirect::route('admin.groups.show', [$groupId])->with('success', t('User was removed from the group.'));
        } catch (Exception $e) {

            return Redirect::route('admin.groups.show', [$groupId])->with('error', t('An error occurred when deleting record.'));
        }
    }

    /**
     * Delete GeoLocateExport Form.
     *
     * @param int $groupId
     * @param int $formId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGeoLocateForm(int $groupId, int $formId): \Illuminate\Http\RedirectResponse
    {
        try {
            $group = $this->model->find($groupId);

            if (! CheckPermission::handle('isOwner', $group)) {
                return Redirect::back();
            }

            $geoLocateForm = GeoLocateForm::withCount('expeditions')->find($formId);

            if ($geoLocateForm->expeditions_count > 0) {

                return Redirect::route('admin.groups.show', [$groupId])
                    ->with('error', t('GeoLocateExport Form cannot be deleted while still being used by Expeditions.'));
            }

            $geoLocateForm->delete();

            return Redirect::route('admin.groups.show', [$groupId])->with('success', t('GeoLocateExport Form was deleted.'));
        } catch (Exception $e) {

            return Redirect::route('admin.groups.show', [$groupId])->with('error', t('There was an error deleteing the GeoLocateExport Form.'));
        }
    }

    /**
     * Check group count for admin welcome/index page.
     *
     * @param $userId
     * @return int
     */
    public function getUserGroupCount($userId): int
    {
        return $this->model->withCount([
            'users' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
        ])->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('users_count')->sum();
    }

    /**
     * Get group select for user.
     *
     * @param $user
     * @return array
     */
    public function getUsersGroupsSelect($user): array
    {
        return $this->model->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('title', 'id')->toArray();
    }

    /**
     * Get group ids for user session.
     *
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserGroupIds($userId): \Illuminate\Support\Collection
    {
        return $this->model->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get()->map(function ($item) {
            return $item['id'];
        });
    }
}
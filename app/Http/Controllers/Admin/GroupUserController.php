<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\Permission\CheckPermission;
use Redirect;
use Throwable;

class GroupUserController extends Controller
{
    /**
     * Delete user from group.
     */
    public function __invoke(Group $group, User $user): \Illuminate\Http\RedirectResponse
    {
        if (! CheckPermission::handle('isOwner', $group)) {
            return Redirect::route('admin.groups.index');
        }

        try {
            if ($group->user_id === $user->id) {
                return Redirect::route('admin.groups.show', [$group])
                    ->with('danger', t('You cannot delete the owner until another owner is selected.'));
            }

            $user->detachGroup($group->id);

            return Redirect::route('admin.groups.show', [$group])->with('success', t('User was removed from the group.'));
        } catch (Throwable $e) {
            return Redirect::route('admin.groups.show', [$group])->with('danger', t('An error occurred when deleting record.'));
        }
    }
}

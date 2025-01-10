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
use App\Http\Requests\InviteFormRequest;
use App\Models\Group;
use App\Notifications\GroupInvite;
use App\Services\Group\GroupInviteService;
use App\Services\Permission\CheckPermission;
use Notification;
use Redirect;
use Throwable;
use View;

/**
 * Class GroupInviteController
 */
class GroupInviteController extends Controller
{
    /**
     * GroupInviteController constructor.
     */
    public function __construct(
        protected GroupInviteService $groupInviteService
    ) {}

    /**
     * Show invite form
     */
    public function create(Group $group): \Illuminate\Contracts\View\View
    {
        $group->load('invites');

        $pass = CheckPermission::handle('isOwner', $group);

        $inviteCount = old('entries', $group->invites->count() ?: 1);

        return View::make('admin.partials.invite-modal-body', compact('group', 'inviteCount', 'pass'));
    }

    /**
     * Send invites to emails
     */
    public function store(Group $group, InviteFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        $group->load('invites');

        try {
            $newInvites = $this->groupInviteService->storeInvites($group, $request->all());

            Notification::send($newInvites, new GroupInvite($group));
            Notification::send($group->invites, new GroupInvite($group));

            return Redirect::back()->with('success', t('Invites to %s sent successfully.', $group->title));
        } catch (Throwable $throwable) {
            return Redirect::back()->with('error', t('Unable to sent invites for %s. Please contact the administration.', $group->title));
        }
    }
}

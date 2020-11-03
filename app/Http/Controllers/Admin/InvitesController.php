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
use App\Services\Model\GroupService;
use App\Repositories\Interfaces\User;
use App\Http\Requests\InviteFormRequest;
use App\Services\Process\InviteProcess;

/**
 * Class InvitesController
 *
 * @package App\Http\Controllers\Admin
 */
class InvitesController extends Controller
{
    /**
     * @var \App\Services\Model\GroupService
     */
    public $groupService;

    /**
     * @var User
     */
    public $userContract;

    /**
     * @var \App\Services\Process\InviteProcess
     */
    private $inviteProcess;

    /**
     * InvitesController constructor.
     *
     * @param \App\Services\Process\InviteProcess $inviteProcess
     * @param \App\Services\Model\GroupService $groupService
     * @param User $userContract
     */
    public function __construct(
        InviteProcess $inviteProcess,
        GroupService $groupService,
        User $userContract
    ) {
        $this->inviteProcess = $inviteProcess;
        $this->groupService = $groupService;
        $this->userContract = $userContract;
    }

    /**
     * Show invite form
     *
     * @param $groupId
     * @return \Illuminate\View\View
     */
    public function index($groupId)
    {
        $group = $this->groupService->findWith($groupId, ['invites']);

        $error = ! $this->checkPermissions('isOwner', $group);
        $inviteCount = old('entries', $group->invites->count() ?: 1);

        return view('admin.partials.invite-modal-body', compact('group', 'inviteCount', 'error'));
    }

    /**
     * Send invites to emails
     *
     * @param InviteFormRequest $request
     * @param $groupId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InviteFormRequest $request, $groupId)
    {
        $group = $this->groupService->findWith($groupId, ['invites']);

        $this->inviteProcess->storeInvites($group->id, $request);

        return redirect()->back();
    }

    /**
     * Resend a group invite
     * @param $groupId
     * @param $inviteId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend($groupId, $inviteId)
    {
        $group = $this->groupService->find($groupId);

        if ( ! $this->checkPermissions('isOwner', $group))
        {
            return redirect()->route('webauth.groups.show', [$groupId]);
        }

        $this->inviteProcess->resendInvite($group, $inviteId);

        return redirect()->route('webauth.invites.index', [$group->id]);
    }
}

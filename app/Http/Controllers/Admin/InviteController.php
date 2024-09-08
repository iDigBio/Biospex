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
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use App\Services\Models\InviteService;

/**
 * Class InviteController
 */
class InviteController extends Controller
{
    /**
     * @var \App\Repositories\GroupRepository
     */
    public $groupRepo;

    /**
     * @var \App\Repositories\UserRepository
     */
    public $userRepo;

    /**
     * @var \App\Services\Models\InviteService
     */
    private $inviteService;

    /**
     * InviteController constructor.
     */
    public function __construct(
        InviteService $inviteService,
        GroupRepository $groupRepo,
        UserRepository $userRepo
    ) {
        $this->inviteService = $inviteService;
        $this->groupRepo = $groupRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Show invite form
     */
    public function index(int $groupId): \Illuminate\View\View
    {
        $group = $this->groupRepo->findWith($groupId, ['invites']);

        $error = ! $this->checkPermissions('isOwner', $group);
        $inviteCount = old('entries', $group->invites->count() ?: 1);

        return \View::make('admin.partials.invite-modal-body', compact('group', 'inviteCount', 'error'));
    }

    /**
     * Send invites to emails
     */
    public function store(InviteFormRequest $request, int $groupId): \Illuminate\Http\RedirectResponse
    {
        $this->inviteService->storeInvites($groupId, $request);

        return back();
    }
}

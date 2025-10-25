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
use App\Jobs\ExportDownloadBatchJob;
use App\Models\Download;
use App\Models\Expedition;
use App\Services\Expedition\ExpeditionService;
use App\Services\Permission\CheckPermission;
use App\Services\User\UserService;
use Auth;
use Redirect;
use View;

/**
 * Class DownloadController
 */
class DownloadController extends Controller
{
    /**
     * DownloadController constructor.
     */
    public function __construct(
        protected UserService $userService,
        protected ExpeditionService $expeditionService,
    ) {}

    /**
     * Index showing downloads for Expedition.
     */
    public function index(Expedition $expedition): \Illuminate\View\View
    {
        $user = Auth::user();
        $this->expeditionService->getExpeditionDownloadsByActor($expedition);

        $error = ! CheckPermission::handle('readProject', $expedition->project->group);

        return View::make('admin.expedition.partials.download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Send request to have export split into batch downloads.
     */
    public function create(Download $download): \Illuminate\Http\RedirectResponse
    {
        $download->load('expedition');

        ExportDownloadBatchJob::dispatch($download);

        return Redirect::route('admin.expeditions.show', [$download->expedition])
            ->with('success', t('Your batch request has been submitted. You will receive an email with download links when the process is complete.'));
    }
}

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
use App\Jobs\TesseractOcrCreateJob;
use App\Services\Models\ProjectModelService;
use App\Services\Models\ExpeditionModelService;

class OcrController extends Controller
{
    /**
     * OcrController constructor.
     *
     * @param \App\Services\Models\ProjectModelService $projectModelService
     * @param \App\Services\Models\ExpeditionModelService $expeditionModelService
     */
    public function __construct(private readonly ProjectModelService $projectModelService, private readonly ExpeditionModelService $expeditionModelService)
    {}

    /**
     * Reprocess OCR.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(int $projectId, int $expeditionId = null): \Illuminate\Http\RedirectResponse
    {
        $group = $expeditionId === null ?
            $this->projectModelService->findWithRelations($projectId, ['group'])->group :
            $this->expeditionModelService->findExpeditionWithRelations($expeditionId, ['project.group'])->project->group;

        if (! $this->checkPermissions('updateProject', $group)) {
            return \Redirect::route('admin.projects.index');
        }

        TesseractOcrCreateJob::dispatch($projectId, $expeditionId);

        $route = $expeditionId === null ? 'admin.projects.show' : 'admin.expeditions.show';
        return \Redirect::route($route, [$projectId, $expeditionId])
            ->with('success', t('OCR processing has been submitted. It may take some time before appearing in the Processes modal. You will be notified by email when the process is complete.'));
    }
}

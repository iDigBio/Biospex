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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Services\Actor\GeoLocate\GeoLocateFormService;
use App\Services\Helpers\GeneralService;
use App\Services\Permission\CheckPermission;
use Request;
use Response;
use Throwable;
use View;

class GeolocateFormController extends Controller
{
    public function __construct(
        protected GeoLocateFormService $geoLocateFormService,
        protected GeneralService $generalService
    ) {}

    /**
     * Show export form in modal.
     */
    public function index(Expedition $expedition): mixed
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $expedition->load('project.group', 'geoLocateExport', 'zooActorExpedition', 'geoActorExpedition');

            $form = $this->geoLocateFormService->getFormData($expedition, ['formId' => $expedition->geo_locate_form_id]);
            $formFields = $this->geoLocateFormService->getFormFields($expedition, $form);

            return View::make('admin.geolocate.partials.form-show', compact('expedition', 'formFields'));
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage().$throwable->getFile().$throwable->getLine()], 500);
        }
    }

    /**
     * Display form when select is changed.
     */
    public function show(Expedition $expedition): mixed
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $expedition->load('project.group', 'geoLocateExport', 'zooActorExpedition', 'geoActorExpedition');

            if (! CheckPermission::handle('readProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $form = $this->geoLocateFormService->getFormData($expedition, Request::all());
            $disabled = $form['exported'] && $this->generalService->downloadFileExists($expedition->geoLocateExport->file, $expedition->geoLocateExport->type, $expedition->geoLocateExport->actor_id);

            return View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'));
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage()], 500);
        }
    }
}

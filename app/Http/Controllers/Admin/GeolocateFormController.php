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

/**
 * This controller handles the rendering and processing of GeoLocate forms.
 */
class GeolocateFormController extends Controller
{
    public function __construct(
        protected GeoLocateFormService $geoLocateFormService,
        protected GeneralService $generalService
    ) {}

    /**
     * Display the form details and render specific fields for the expedition.
     *
     * @param  Expedition  $expedition  The expedition instance containing necessary data.
     * @return mixed Returns the rendered view with form fields, or a JSON response detailing any errors that occur.
     */
    public function index(Expedition $expedition): mixed
    {
        // if (! Request::ajax()) {
        //    return Response::json(['message' => t('Request must be ajax.')], 400);
        // }

        try {
            $this->geoLocateFormService->loadExpeditionRelations($expedition);
            $form = $this->geoLocateFormService->getFormData($expedition);
            $formFields = $this->geoLocateFormService->buildFormFields($expedition, $form);
            $selectedForm = isset($expedition->geoLocateDataSource->geoLocateForm) ? $expedition->geoLocateDataSource->geoLocateForm->id : null;

            return View::make('admin.geolocate.partials.form-show', compact('expedition', 'formFields', 'selectedForm'));
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage().$throwable->getFile().$throwable->getLine()], 500);
        }
    }

    /**
     * Display form when select is changed.
     */
    public function show(Expedition $expedition): mixed
    {
        // if (! Request::ajax()) {
        //    return Response::json(['message' => t('Request must be ajax.')], 400);
        // }

        try {
            $this->geoLocateFormService->loadExpeditionRelations($expedition);

            if (! CheckPermission::handle('readProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            // Request will contain formId and source
            $form = $this->geoLocateFormService->getFormData($expedition, Request::all());

            $disabled = $this->geoLocateFormService->checkExportDisabled($expedition, $form);

            return View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'));
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage()], 500);
        }
    }
}

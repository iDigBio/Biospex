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
use App\Models\Expedition;
use App\Services\Actor\GeoLocate\GeoLocateFormService;
use App\Services\Permission\CheckPermission;
use Request;
use Response;
use Throwable;
use View;

class GeolocateFieldsController extends Controller
{
    public function __construct(protected GeoLocateFormService $geoLocateFormService) {}

    /**
     * Retrieve form fields for selected form.
     */
    public function __invoke(Expedition $expedition): mixed
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $this->geoLocateFormService->loadExpeditionRelations($expedition);

            if (! CheckPermission::handle('readProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permission.')], 401);
            }

            // Request will contain formId and source
            $form = $this->geoLocateFormService->getFormData($expedition, Request::all());

            // Create a temporary view to render the Livewire component
            return view('admin.geolocate.partials.geolocate-livewire', compact('expedition', 'form'))->render();
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage().$throwable->getFile().$throwable->getLine()], 500);
        }
    }
}

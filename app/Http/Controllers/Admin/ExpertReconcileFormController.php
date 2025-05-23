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
use App\Services\Reconcile\ReconcileUserFormService;
use Request;
use Response;
use View;

class ExpertReconcileFormController extends Controller
{
    /**
     * Show the form for uploading user file for reconciliation.
     */
    public function edit(Expedition $expedition): \Illuminate\View\View
    {
        return View::make('admin.reconcile.partials.upload', compact('expedition'));
    }

    /**
     * Upload user file for reconciliation.
     */
    public function update(Expedition $expedition, ReconcileUserFormService $reconcileUserFormService): \Illuminate\Http\JsonResponse
    {
        $expedition->load('project');

        if (! Request::hasFile('file') || Request::file('file')->getClientOriginalExtension() !== 'csv') {
            return Response::json(['error' => true, 'message' => t('File must be a CSV.')]);
        }

        $result = $reconcileUserFormService->reconciledWithUserFile($expedition);

        return $result ?
            Response::json(['message' => t('File upload was successful. It will now be listed in your downloads section of the Expedition.')]) :
            Response::json(['error' => true, 'message' => t('Error uploading file. Please try again or contact the Administration.')]);
    }
}

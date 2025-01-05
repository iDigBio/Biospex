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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Project\ProjectService;
use Request;
use Response;
use View;

class ProjectSortController extends Controller
{
    /**
     * ProjectSortController constructor.
     */
    public function __construct(protected ProjectService $projectService) {}

    /**
     * Admin Projects page sort and order.
     */
    public function __invoke(): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $projects = $this->projectService->getPublicIndex(Request::all());

        return View::make('front.project.partials.project', compact('projects'));
    }
}

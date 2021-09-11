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

namespace App\Http\Controllers\Front;

use Flash;
use App\Http\Controllers\Controller;
use App\Services\Model\ResourceService;
use Storage;

/**
 * Class ResourceController
 *
 * @package App\Http\Controllers\Front
 */
class ResourceController extends Controller
{
    /**
     * Show resources.
     *
     * @param \App\Services\Model\ResourceService $resourceService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ResourceService $resourceService)
    {
        $resources = $resourceService->getResourcesOrdered();

        return view('front.resource.index', compact('resources'));
    }

    /**
     * Download resource file.
     *
     * @param \App\Services\Model\ResourceService $resourceService
     * @param $resourceId
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(ResourceService $resourceService, $resourceId)
    {
        $download = $resourceService->find($resourceId);

        if (! $download->document->exists() || ! file_exists(public_path('storage' . $download->document->path()))) {
            Flash::error('File cannot be found.');

            return redirect()->route('front.resources.index');
        }

        return Storage::download('public/' . $download->document->path());
    }
}

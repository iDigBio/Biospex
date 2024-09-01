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

use App\Http\Controllers\Controller;
use App\Models\Resource;
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
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Resource $resource)
    {
        $resources = $resource->orderBy('order', 'asc')->get();

        return \View::make('front.resource.index', compact('resources'));
    }

    /**
     * Download resource file.
     *
     * @param \App\Models\Resource $resource
     * @param $resourceId
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Resource $resource, $resourceId)
    {
        $download = $resource->find($resourceId);

        if (! $download->document->exists() || ! file_exists(public_path('storage' . $download->document->path()))) {

            return \Redirect::route('front.resources.index')->with('error', t('File cannot be found.'));
        }

        return Storage::download('public/' . $download->document->path());
    }
}

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
use Illuminate\Http\RedirectResponse;
use Redirect;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use View;

/**
 * Class ResourceController
 */
class ResourceController extends Controller
{
    /**
     * Show resources.
     */
    public function index(Resource $resource): \Illuminate\Contracts\View\View
    {
        $resources = $resource->orderBy('order', 'asc')->get();

        return View::make('front.resource.index', compact('resources'));
    }

    /**
     * Download resource file.
     */
    public function show(Resource $resource): RedirectResponse|StreamedResponse
    {
        if (! $resource->document->exists() || ! file_exists(public_path('storage'.$resource->document->path()))) {

            return Redirect::route('front.resources.index')->with('danger', t('File cannot be found.'));
        }

        return Storage::download('public/'.$resource->document->path());
    }
}

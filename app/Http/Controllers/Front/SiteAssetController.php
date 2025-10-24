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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SiteAsset;
use Illuminate\Http\RedirectResponse;
use Redirect;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use View;

/**
 * Class SiteAssetController
 */
class SiteAssetController extends Controller
{
    /**
     * Show assets.
     */
    public function index(SiteAsset $siteAsset): \Illuminate\Contracts\View\View
    {
        $assets = $siteAsset->orderBy('order', 'asc')->get();

        return View::make('front.site-asset.index', compact('assets'));
    }

    /**
     * Download asset file.
     */
    public function show(SiteAsset $siteAsset): RedirectResponse|StreamedResponse
    {
        if (! $siteAsset->document->exists() || ! file_exists(public_path('storage'.$siteAsset->document->path()))) {

            return Redirect::route('front.site-assets.index')
                ->with('danger', t('File cannot be found.'));
        }

        return Storage::download('public/'.$siteAsset->document->path());
    }
}

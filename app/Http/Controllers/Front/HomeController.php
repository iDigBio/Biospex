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
use App\Services\Expedition\ExpeditionService;
use App\Services\Transcriptions\PanoptesTranscriptionService;
use Response;
use Storage;
use View;

/**
 * Class HomeController
 */
class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(
        ExpeditionService $expeditionService,
        PanoptesTranscriptionService $panoptesTranscriptionService
    ): \Illuminate\Contracts\View\View {

        $expedition = $expeditionService->getHomePageProjectExpedition();
        $contributorCount = $panoptesTranscriptionService->getContributorCount();
        $transcriptionCount = $panoptesTranscriptionService->getTotalTranscriptions();

        return View::make('front.home', compact('expedition', 'contributorCount', 'transcriptionCount'));
    }

    /**
     * Get tmp images for fossil project.
     * TODO: These images are hosted on Biospex for Project 115: "Digitizing the Biology Collection of the Science Museum of Minnesota".
     */
    public function tmpimage(string $name): \Illuminate\Http\Response
    {
        $exists = Storage::disk('public')->exists('tmpimage/'.$name);

        if ($exists) {
            // get content of image
            $content = Storage::get('public/tmpimage/'.$name);

            // get mime type of image
            $mime = Storage::mimeType('public/tmpimage/'.$name);      // prepare response with image content and response code
            $response = Response::make($content, 200);      // set header
            $response->header('Content-Type', $mime);      // return response

            return $response;
        } else {
            abort(404);
        }
    }
}

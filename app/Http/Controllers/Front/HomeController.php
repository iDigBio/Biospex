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
use App\Services\Model\ExpeditionService;
use App\Services\Model\PanoptesTranscriptionService;

/**
 * Class HomeController
 *
 * @package App\Http\Controllers\Front
 */
class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Model\PanoptesTranscriptionService $panoptesTranscriptionService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ExpeditionService $expeditionService, PanoptesTranscriptionService $panoptesTranscriptionService)
    {
        $expedition = $expeditionService->getHomePageProjectExpedition();
        $contributorCount = $panoptesTranscriptionService->getContributorCount();
        $transcriptionCount = $panoptesTranscriptionService->getTotalTranscriptions();

        return view('front.home', compact('expedition', 'contributorCount', 'transcriptionCount'));
    }

    /**
     * Get tmp images for fossil project.
     * TODO remove after project 115 completed.
     * @param string $name
     * @return \Illuminate\Http\Response|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function tmpimage(string $name)
    {
        $exists = \Storage::disk('public')->exists('tmpimage/'.$name);

        if($exists) {
            //get content of image
            $content = \Storage::get('public/tmpimage/'.$name);

            //get mime type of image
            $mime = \Storage::mimeType('public/tmpimage/'.$name);      //prepare response with image content and response code
            $response = \Response::make($content, 200);      //set header
            $response->header("Content-Type", $mime);      // return response
            return $response;
        } else {
            abort(404);
        }
    }
}

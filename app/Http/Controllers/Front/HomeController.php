<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $panoptesTranscription
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract, PanoptesTranscription $panoptesTranscription)
    {
        $expedition = $expeditionContract->getHomePageProjectExpedition();
        $contributorCount = $panoptesTranscription->getContributorCount();
        $transcriptionCount = $panoptesTranscription->getTotalTranscriptions();

        return view('front.home', compact('expedition', 'contributorCount', 'transcriptionCount'));
    }
}

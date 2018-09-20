<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Expedition;

class ExpeditionsController extends Controller
{
    /**
     * Display all expeditions for user.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract)
    {
        $expeditions = $expeditionContract->getExpeditions();

        return view('frontend.expeditions.index', compact('expeditions', 'user'));
    }
}

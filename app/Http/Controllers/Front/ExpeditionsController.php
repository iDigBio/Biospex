<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Expedition;

class ExpeditionsController extends Controller
{
    /**
     * Displays Expeditions on public page.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract, $sort = null, $order = null)
    {
        $expeditions = $expeditionContract->getExpeditionPublicPage($sort, $order);

        return request()->ajax() ?
            view('front.expedition.partials.expedition', compact('expeditions')) :
            view('front.expedition.index', compact('expeditions'));
    }

    /**
     * Displays Completed Expeditions on public page.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function completed(Expedition $expeditionContract, $sort = null, $order = null)
    {
        $expeditions = $expeditionContract->getExpeditionCompletedPublicPage($sort, $order);

        return view('front.expedition.partials.expedition', compact('expeditions'));
    }
}

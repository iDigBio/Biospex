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
        $expeditions = $expeditionContract->getExpeditionPublicPage();
        $expeditionsCompleted = $expeditionContract->getExpeditionCompletedPublicPage();

        return view('front.expedition.index', compact('expeditions', 'expeditionsCompleted'));
    }

    /**
     * Displays Completed Expeditions on public page.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(Expedition $expeditionContract)
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $name = request()->get('name');
        $sort = request()->get('sort');
        $order = request()->get('order');

        $expeditions = $name === 'active' ?
            $expeditions = $expeditionContract->getExpeditionPublicPage($sort, $order) :
            $expeditions = $expeditionContract->getExpeditionCompletedPublicPage($sort, $order);

        return view('front.expedition.partials.expedition', compact('expeditions'));
    }
}

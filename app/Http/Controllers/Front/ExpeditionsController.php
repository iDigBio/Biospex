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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Expedition $expeditionContract)
    {
        $results = $expeditionContract->getExpeditionPublicIndex();

        list($expeditions, $expeditionsCompleted) = $results->partition(function($expedition) {
            return $expedition->nfnActor->pivot->completed === 0;
        });

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

        $type = request()->get('type');
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        list($active, $completed) = $expeditionContract->getExpeditionPublicIndex($sort, $order, $projectId)
            ->partition(function($expedition) {
                return $expedition->nfnActor->pivot->completed === 0;
        });

        $expeditions = $type === 'active' ? $active : $completed;

        return view('front.expedition.partials.expedition', compact('expeditions'));
    }
}

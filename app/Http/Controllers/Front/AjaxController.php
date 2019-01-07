<?php 

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AmChart;
use App\Repositories\Interfaces\Event;
use Artisan;

class AjaxController extends Controller
{
    /**
     * Load AmChart for project home page.
     *
     * @param AmChart $amChartContract
     * @param $projectId
     * @return mixed
     */
    public function loadAmChart(AmChart $amChartContract, $projectId)
    {
        /*
        if (! request()->ajax() || $projectId === null) {
            return response()->json(['html' => 'hitting null']);
        }
        */

        $record = $amChartContract->findBy('project_id', $projectId);

        return json_decode($record->data);
    }

    /**
     * Call polling command when process modal opened. Trigger inside biospex.js
     */
    public function poll()
    {
        if (request()->ajax()) {
            Artisan::call('ocr:poll');
            Artisan::call('export:poll');
        }
    }

    /**
     * @param $eventId
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function scoreboard(Event $eventContract, $eventId)
    {
        $event = $eventContract->getEventScoreboard($eventId, ['id']);

        if (! request()->ajax() || is_null($event)) {
            return response()->json(['html' => 'Error retrieving the Event']);
        }

        return view('front.event.partials.scoreboard-content', ['event' => $event]);
    }
}

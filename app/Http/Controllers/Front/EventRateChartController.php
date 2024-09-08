<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Chart\BiospexEventRateChartProcess;
use Illuminate\Http\JsonResponse;

class EventRateChartController extends Controller
{
    /**
     * Display for event step charts.
     */
    public function index(BiospexEventRateChartProcess $service, string $eventId, ?string $timestamp = null): JsonResponse
    {
        $result = $service->eventStepChart($eventId, $timestamp);

        return response()->json($result);
    }
}

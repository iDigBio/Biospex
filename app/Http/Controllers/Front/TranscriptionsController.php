<?php declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AmChart;

class TranscriptionsController extends Controller
{
    /**
     * Return json data for transcription charts.
     *
     * @param \App\Repositories\Interfaces\AmChart $amChartContract
     * @param string $projectId
     * @param string $year
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function index(AmChart $amChartContract, string $projectId, string $year)
    {
        $chart = $amChartContract->findBy('project_id', $projectId);

        $file = json_decode(\File::get(config('config.project_chart_config')), true);
        $file['series'] = $chart->series[$year];
        $file['data'] = $chart->data[$year];

        return response()->json($file);
    }
}

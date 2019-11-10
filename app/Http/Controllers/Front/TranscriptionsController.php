<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AmChart;

class TranscriptionsController extends Controller
{
    public function __construct()
    {

    }

    /**
     * @param \App\Repositories\Interfaces\AmChart $amChartContract
     * @param $projectId
     * @param $year
     * @return false|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function index(AmChart $amChartContract, $projectId, $year)
    {
        $chart = $amChartContract->findBy('project_id', $projectId);

        $file = json_decode(\File::get(config('config.project_chart_config')), true);
        $file['series'] = $chart->series->{$year};
        $file['data'] = $chart->data->{$year};

        return json_encode(['config' => $file]);
    }
}

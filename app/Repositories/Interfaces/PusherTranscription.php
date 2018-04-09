<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface PusherTranscription extends RepositoryInterface
{

    /**
     * @param Request $request
     * @param bool $count
     * @return mixed
     */
    public function getWeDigBioDashboardApi(Request $request, $count = false);
}

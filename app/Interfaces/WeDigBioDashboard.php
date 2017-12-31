<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface WeDigBioDashboard extends Eloquent
{

    /**
     * @param Request $request
     * @param bool $count
     * @return mixed
     */
    public function getWeDigBioDashboardApi(Request $request, $count = false);
}

<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewExpeditions;
use App\Nova\Metrics\NewProjects;
use App\Nova\Metrics\NewTranscriptions;
use App\Nova\Metrics\NewUsers;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new NewUsers(),
            new NewProjects(),
            new NewExpeditions(),
            new NewTranscriptions(),
        ];
    }
}

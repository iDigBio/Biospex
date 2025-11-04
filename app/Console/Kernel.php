<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console;

use Cache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Class Kernel
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [//
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('export:queue')->everyTwoMinutes();

        /*
        $schedule->command('cache:prune-stale-tags')->hourly();

        // $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();

        // Run ocr every 2 minutes.
        $schedule->command('export:queue')->everyTwoMinutes();
        $schedule->command('tesseract:ocr-process')->everyTwoMinutes();

        // Clean bingo maps
        $schedule->command('bingo:clean')->dailyAt('10:05');

        // Run lambda-reconciliation check every morning at 6:00
        $schedule->command('app:check-lambda-reconcile')->dailyAt('6:00');

        if ($this->app->environment('production')) {
            // Trigger workflow manager to handle csv creation and updating expedition/project
            $schedule->command('workflow:manage')->daily()->before(function () {
                Cache::flush();
                Artisan::call('lada-cache:flush');
            });

            // Clean efs directories for files over 72 hours old.
            // $schedule->command('app:clean-efs-dirs')->daily();
        }
        */
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console/console.php');
    }
}

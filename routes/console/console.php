<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('logs:clear', function() {

    exec('rm ' . storage_path('logs/*.log'));

    $this->comment('Logs have been cleared!');

})->describe('Clear log files');

Artisan::command('inspire', function () {
    $router->comment(Inspiring::quote());
})->describe('Display an inspiring quote');
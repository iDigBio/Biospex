<?php
/**
 * Not using package until https://github.com/fico7489/laravel-pivot/issues/55 is fixed
 */
namespace App\Models\Watchers;

use Laravel\Telescope\Watchers\ModelWatcher as PackageWatcher;

class ModelWatcher extends PackageWatcher
{
    public function recordAction($event, $data)
    {
        //Catch and convert model events from some non-framework model event implementations such as
        //genealabs/laravel-model-caching and fico7489/laravel-pivot
        //Tested with fico7489/laravel-pivot v3.0.0 on 2019-02-02
        $modifiedData = $data;
        $modifiedData[0] = $modifiedData[0] ?? $modifiedData['model'];

        parent::recordAction($event, $modifiedData);
    }
}
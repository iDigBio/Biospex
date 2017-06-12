<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\App;

class ImportServiceFactory
{
    /**
     * Create import class to run.
     *
     * @param $class
     * @return bool
     */
    public function create($class)
    {
        $nameSpace = 'App\Services\Import\\';
        if (class_exists($nameSpace . $class)) {
            return App::make($nameSpace . $class);
        }

        return false;
    }
}


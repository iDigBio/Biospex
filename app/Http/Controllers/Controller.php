<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check permissions.
     *
     * @param $ability
     * @param $objects
     * @return bool
     */
    public function checkPermissions($ability, $objects)
    {
        if (request()->user()->cannot($ability, $objects))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return false;
        }

        return true;
    }
}

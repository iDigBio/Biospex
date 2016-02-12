<?php

namespace Biospex\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check permissions.
     * @param $user
     * @param $classes
     * @param $ability
     * @return bool
     */
    public function checkPermissions($user, $classes, $ability)
    {
        if ($user->cannot($ability, $classes))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return false;
        }

        return true;
    }
}

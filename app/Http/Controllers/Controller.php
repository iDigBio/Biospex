<?php

namespace App\Http\Controllers;

use Flash;
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
     * @param $object
     * @return bool
     */
    public function checkPermissions($ability, $object = null)
    {
        try{
            $this->authorize($ability, $object);
        }
        catch (\Exception $e)
        {
            \Flash::warning(t('You do not have sufficient permissions.'));

            return false;
        }

        return true;
    }
}

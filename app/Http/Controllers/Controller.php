<?php

namespace App\Http\Controllers;

use Flash;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
/*
 * index
 * create
 * store
 * show
 * edit
 * update
 * destroy
 */
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
    public function checkPermissions($ability, $object = null): bool
    {
        try{
            $this->authorize($ability, $object);
        }
        catch (\Throwable $throwable)
        {
            session()->flash('error', t('You do not have sufficient permissions.'));

            return false;
        }

        return true;
    }
}

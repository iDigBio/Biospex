<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

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
     */
    public function checkPermissions($ability, $object = null): bool
    {
        try {
            $this->authorize($ability, $object);
        } catch (\Throwable $throwable) {
            session()->flash('error', t('You do not have sufficient permissions.'));

            return false;
        }

        return true;
    }
}

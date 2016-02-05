<?php namespace Biospex\Services\Common;

class PermissionService
{
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